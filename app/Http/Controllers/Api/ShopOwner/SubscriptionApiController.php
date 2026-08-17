<?php

namespace App\Http\Controllers\Api\ShopOwner;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\SubscriptionPlan;
use App\Models\Subscription;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Razorpay\Api\Api as RazorpayApi;

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
     * end date has already passed.
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
     * them to the Free plan immediately.
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

        // If user is choosing the free plan, perform downgrade/upgrade immediately
        if ($plan->slug === 'free') {
            $user->active_plan_id = $plan->id;
            $user->save();

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
            ]);
        } catch (\Exception $e) {
            Log::error('Razorpay Subscription Creation Failed: ' . $e->getMessage());

            // Fallback: Try creating a Razorpay Order for one-time checkout
            try {
                $api = new RazorpayApi($keyId, $keySecret);
                $orderData = [
                    'receipt' => 'sub_rcpt_' . $user->id . '_' . time(),
                    'amount' => (int)($plan->price * 100), // paise
                    'currency' => 'INR',
                    'notes' => [
                        'user_id' => $user->id,
                        'plan_slug' => $plan->slug,
                        'plan_id' => $plan->id
                    ]
                ];
                $razorpayOrder = $api->order->create($orderData);

                return response()->json([
                    'requires_payment' => true,
                    'gateway' => 'razorpay',
                    'key_id' => $keyId,
                    'order_id' => $razorpayOrder['id'],
                    'amount' => $orderData['amount'],
                    'currency' => 'INR',
                    'plan' => $plan,
                    'user' => [
                        'name' => $user->name,
                        'email' => $user->email,
                        'mobile' => $user->mobile,
                    ]
                ]);
            } catch (\Exception $orderEx) {
                Log::error('Razorpay Fallback Order Creation Failed: ' . $orderEx->getMessage());
                return response()->json(['message' => 'Failed to initiate payment gateway: ' . $e->getMessage()], 500);
            }
        }
    }

    /**
     * Verify payment signature and activate the subscription.
     */
    public function verifyPayment(Request $request)
    {
        $request->validate([
            'plan_slug' => 'required|string|in:premium,business',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
            'razorpay_subscription_id' => 'nullable|string',
            'razorpay_order_id' => 'nullable|string',
        ]);

        $user = $request->user();
        $plan = SubscriptionPlan::where('slug', $request->plan_slug)->first();

        if (!$plan) {
            return response()->json(['message' => 'Subscription plan not found.'], 404);
        }

        $paymentId = $request->razorpay_payment_id;
        $signature = $request->razorpay_signature;
        $subscriptionId = $request->razorpay_subscription_id;
        $orderId = $request->razorpay_order_id;

        $keySecret = config('services.razorpay.key_secret');

        // Signature verification
        $verified = false;
        if (!empty($subscriptionId)) {
            $expectedSignature = hash_hmac('sha256', $paymentId . '|' . $subscriptionId, $keySecret);
            $verified = hash_equals($expectedSignature, $signature);
        } elseif (!empty($orderId)) {
            $expectedSignature = hash_hmac('sha256', $orderId . '|' . $paymentId, $keySecret);
            $verified = hash_equals($expectedSignature, $signature);
        }

        if (!$verified) {
            return response()->json(['message' => 'Payment signature verification failed.'], 400);
        }

        // Activate the subscription locally
        $user->active_plan_id = $plan->id;
        $user->save();

        $shop = $user->shops()->first();
        if (!$shop) {
            $shop = \App\Models\Shop::create([
                'owner_id' => $user->id,
                'name' => $user->name . "'s Shop",
                'status' => 'active'
            ]);
        }

        $endsAt = null;
        if ($plan->slug === 'business') {
            $endsAt = now()->addYears(100);
        } elseif ($plan->billing_period === 'yearly') {
            $endsAt = now()->addYear();
        } elseif ($plan->billing_period === 'monthly') {
            $endsAt = now()->addMonth();
        }

        $subscription = $user->subscriptions()->updateOrCreate(
            ['status' => 'active'],
            [
                'shop_id' => $shop->id,
                'plan_id' => $plan->id,
                'starts_at' => now(),
                'ends_at' => $endsAt,
                'status' => 'active',
            ]
        );

        // Record Payment
        $transactionId = $subscriptionId ?? $orderId ?? $paymentId;
        Payment::updateOrCreate(
            ['transaction_id' => $transactionId],
            [
                'user_id' => $user->id,
                'shop_id' => $shop->id,
                'plan_id' => $plan->id,
                'amount' => $plan->price,
                'payment_gateway' => 'razorpay',
                'status' => 'successful',
                'payment_date' => now(),
            ]
        );

        $user->load(['activePlan', 'currentSubscription']);

        return response()->json([
            'message' => 'Subscription activated successfully.',
            'plan' => $user->activePlan,
            'subscription' => $user->currentSubscription,
            'user' => $user,
            'shop_count' => $user->shops()->count(),
        ]);
    }

    /**
     * Handle Razorpay Webhook events.
     */
    public function handleWebhook(Request $request)
    {
        $webhookSecret = env('RAZORPAY_WEBHOOK_SECRET', '');
        if (!empty($webhookSecret)) {
            $payload = $request->getContent();
            $expectedSignature = hash_hmac('sha256', $payload, $webhookSecret);
            $actualSignature = $request->header('X-Razorpay-Signature');
            if ($expectedSignature !== $actualSignature) {
                return response()->json(['message' => 'Invalid webhook signature'], 400);
            }
        }

        $event = $request->input('event');
        $payload = $request->input('payload');

        if (in_array($event, ['subscription.charged', 'subscription.activated'])) {
            $subEntity = $payload['subscription']['entity'] ?? null;
            $paymentEntity = $payload['payment']['entity'] ?? null;

            if ($subEntity) {
                $userId = $subEntity['notes']['user_id'] ?? null;
                $planSlug = $subEntity['notes']['plan_slug'] ?? null;
                $razorpaySubId = $subEntity['id'];
                $paymentId = $paymentEntity['id'] ?? $razorpaySubId;

                if ($userId && $planSlug) {
                    $user = \App\Models\User::find($userId);
                    $plan = SubscriptionPlan::where('slug', $planSlug)->first();

                    if ($user && $plan) {
                        $user->active_plan_id = $plan->id;
                        $user->save();

                        $shop = $user->shops()->first();
                        if (!$shop) {
                            $shop = \App\Models\Shop::create([
                                'owner_id' => $user->id,
                                'name' => $user->name . "'s Shop",
                                'status' => 'active'
                            ]);
                        }

                        $endsAt = null;
                        if ($plan->slug === 'business') {
                            $endsAt = now()->addYears(100);
                        } elseif ($plan->billing_period === 'yearly') {
                            $endsAt = now()->addYear();
                        } elseif ($plan->billing_period === 'monthly') {
                            $endsAt = now()->addMonth();
                        }

                        $user->subscriptions()->updateOrCreate(
                            ['status' => 'active'],
                            [
                                'shop_id' => $shop->id,
                                'plan_id' => $plan->id,
                                'starts_at' => now(),
                                'ends_at' => $endsAt,
                                'status' => 'active',
                            ]
                        );

                        // Record Payment
                        Payment::updateOrCreate(
                            ['transaction_id' => $paymentId],
                            [
                                'user_id' => $user->id,
                                'shop_id' => $shop->id,
                                'plan_id' => $plan->id,
                                'amount' => $plan->price,
                                'payment_gateway' => 'razorpay',
                                'status' => 'successful',
                                'payment_date' => now(),
                            ]
                        );

                        Log::info("Webhook processed: User {$user->id} upgraded to plan {$plan->name}");
                    }
                }
            }
        }

        return response()->json(['status' => 'success']);
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

