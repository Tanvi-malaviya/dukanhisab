<?php

namespace App\Http\Controllers\Api\ShopOwner;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SubscriptionApiController extends Controller
{
    /**
     * All purchasable plans (Free/Monthly/Yearly), for the app's Premium
     * screen to render pricing and feature limits.
     */
    public function plans(Request $request)
    {
        $plans = SubscriptionPlan::where('status', 'active')
            ->orderBy('price')
            ->get();

        return response()->json(['plans' => $plans]);
    }

    /**
     * The authenticated user's current plan, subscription record, and
     * live shop usage — so the app can show "1 of 3 shops used" etc.
     * Lazily downgrades to the Free plan if the active subscription's
     * end date has already passed (no scheduled job runs this — it's
     * self-healing on read, so the app never shows a stale paid plan).
     */
    public function current(Request $request)
    {
        $user = $request->user();
        $user->load(['activePlan', 'currentSubscription']);

        $subscription = $user->currentSubscription;
        if ($subscription && $subscription->status === 'active' && $subscription->isExpired()) {
            $this->downgradeToFree($user, $subscription, 'expired');
            $user->load(['activePlan', 'currentSubscription']);
        }

        return response()->json([
            'plan' => $user->activePlan,
            'subscription' => $user->currentSubscription,
            'shop_count' => $user->shops()->count(),
        ]);
    }

    /**
     * Let the user cancel their own active paid subscription — reverts
     * them to the Free plan immediately (no partial-period access, since
     * there's no recurring billing to stop yet).
     */
    public function cancel(Request $request)
    {
        $user = $request->user();
        $user->load(['activePlan', 'currentSubscription']);

        $subscription = $user->currentSubscription;
        if (!$subscription || $subscription->status !== 'active' || !$user->activePlan || $user->activePlan->slug === 'free') {
            return response()->json(['message' => 'You do not have an active paid subscription to cancel.'], 400);
        }

        $this->downgradeToFree($user, $subscription, 'cancelled');
        $user->load(['activePlan', 'currentSubscription']);

        return response()->json([
            'message' => 'Your subscription has been cancelled. You are now on the Free plan.',
            'plan' => $user->activePlan,
            'subscription' => $user->currentSubscription,
            'user' => $user,
            'shop_count' => $user->shops()->count(),
        ]);
    }

    /**
     * Create a Razorpay Order for purchasing a paid subscription plan.
     */
    public function createOrder(Request $request)
    {
        $request->validate([
            'plan_slug' => 'required|string|in:premium,business',
        ]);

        $user = $request->user();
        $plan = SubscriptionPlan::where('slug', $request->plan_slug)->first();

        if (!$plan) {
            return response()->json(['message' => 'Subscription plan not found.'], 404);
        }

        $amountInPaise = (int) round(((float) $plan->price) * 100);
        $keyId = config('services.razorpay.key');
        $keySecret = config('services.razorpay.secret');

        // If Razorpay API credentials are configured, create order via Razorpay API
        if (!empty($keyId) && !empty($keySecret)) {
            try {
                $response = Http::withBasicAuth($keyId, $keySecret)->post('https://api.razorpay.com/v1/orders', [
                    'amount' => $amountInPaise,
                    'currency' => 'INR',
                    'receipt' => 'sub_rcpt_' . $user->id . '_' . time(),
                    'notes' => [
                        'user_id' => (string) $user->id,
                        'plan_id' => (string) $plan->id,
                        'plan_slug' => $plan->slug,
                    ],
                ]);

                if ($response->successful()) {
                    $order = $response->json();
                    return response()->json([
                        'order_id' => $order['id'],
                        'key' => $keyId,
                        'amount' => $amountInPaise,
                        'currency' => 'INR',
                        'plan' => $plan,
                        'user' => [
                            'name' => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')),
                            'email' => $user->email,
                            'mobile' => $user->mobile,
                        ],
                    ]);
                } else {
                    Log::error('Razorpay order creation failed', ['response' => $response->body()]);
                    return response()->json([
                        'message' => 'Razorpay Error: ' . ($response->json('error.description') ?? 'Failed to create order.'),
                    ], 400);
                }
            } catch (\Exception $e) {
                Log::error('Razorpay connection error: ' . $e->getMessage());
                return response()->json(['message' => 'Failed to connect to payment gateway: ' . $e->getMessage()], 500);
            }
        }

        // Test/Sandbox fallback if Razorpay keys are not yet configured in .env
        $mockOrderId = 'order_mock_' . bin2hex(random_bytes(8));
        return response()->json([
            'order_id' => $mockOrderId,
            'key' => 'rzp_test_placeholder',
            'amount' => $amountInPaise,
            'currency' => 'INR',
            'is_test_mode' => true,
            'plan' => $plan,
            'user' => [
                'name' => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')),
                'email' => $user->email,
                'mobile' => $user->mobile,
            ],
        ]);
    }

    /**
     * Verify payment signature and activate subscription.
     */
    public function verifyPayment(Request $request)
    {
        $request->validate([
            'plan_slug' => 'required|string|in:premium,business',
            'razorpay_order_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'nullable|string',
        ]);

        $user = $request->user();
        $plan = SubscriptionPlan::where('slug', $request->plan_slug)->first();

        if (!$plan) {
            return response()->json(['message' => 'Subscription plan not found.'], 404);
        }

        $keySecret = config('services.razorpay.secret');

        // Signature verification when secret is set and not in mock mode
        if (!empty($keySecret) && !str_starts_with($request->razorpay_order_id, 'order_mock_')) {
            $expectedSignature = hash_hmac(
                'sha256',
                $request->razorpay_order_id . '|' . $request->razorpay_payment_id,
                $keySecret
            );

            if (!hash_equals($expectedSignature, (string) $request->razorpay_signature)) {
                return response()->json(['message' => 'Payment verification failed: Invalid signature.'], 400);
            }
        }

        // Record successful payment in payments table
        Payment::create([
            'user_id' => $user->id,
            'shop_id' => $user->shops()->first()?->id ?? 1,
            'plan_id' => $plan->id,
            'amount' => $plan->price,
            'payment_gateway' => 'razorpay',
            'transaction_id' => $request->razorpay_payment_id,
            'status' => 'successful',
            'payment_date' => now(),
        ]);

        // Activate Plan on User
        $user->active_plan_id = $plan->id;
        $user->save();

        // Create or update subscription record
        $user->subscriptions()->updateOrCreate(
            ['status' => 'active'],
            [
                'plan_id' => $plan->id,
                'starts_at' => now(),
                'ends_at' => $plan->slug === 'premium' ? now()->addYear() : null,
                'status' => 'active',
            ]
        );

        $user->load(['activePlan', 'currentSubscription']);

        return response()->json([
            'message' => 'Payment successful! ' . $plan->name . ' activated successfully.',
            'plan' => $user->activePlan,
            'subscription' => $user->currentSubscription,
            'user' => $user,
            'shop_count' => $user->shops()->count(),
        ]);
    }

    public function upgrade(Request $request)
    {
        $request->validate([
            'plan_slug' => 'required|string|in:free,premium,business',
        ]);

        $user = $request->user();
        $plan = SubscriptionPlan::where('slug', $request->plan_slug)->first();

        if (!$plan) {
            return response()->json(['message' => 'Subscription plan not found.'], 404);
        }

        $user->active_plan_id = $plan->id;
        $user->save();

        if ($plan->slug === 'free') {
            // Cancel current active subscription if any
            $activeSub = $user->subscriptions()->where('status', 'active')->first();
            if ($activeSub) {
                $this->downgradeToFree($user, $activeSub, 'cancelled');
            }
        } else {
            // Create or update subscription record
            $user->subscriptions()->updateOrCreate(
                ['status' => 'active'],
                [
                    'plan_id' => $plan->id,
                    'starts_at' => now(),
                    'ends_at' => $plan->slug === 'premium' ? now()->addYear() : null,
                    'status' => 'active',
                ]
            );
        }

        $user->load(['activePlan', 'currentSubscription']);

        return response()->json([
            'message' => 'Subscription updated successfully to ' . $plan->name,
            'plan' => $user->activePlan,
            'subscription' => $user->currentSubscription,
            'user' => $user,
            'shop_count' => $user->shops()->count(),
        ]);
    }

    private function downgradeToFree(\App\Models\User $user, \App\Models\Subscription $subscription, string $reason): void
    {
        $subscription->update([
            'status' => $reason,
            'ends_at' => now(),
        ]);

        $freePlan = SubscriptionPlan::where('slug', 'free')->first();
        $user->update(['active_plan_id' => $freePlan?->id]);
    }
}
