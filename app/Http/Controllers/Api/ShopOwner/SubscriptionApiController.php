<?php

namespace App\Http\Controllers\Api\ShopOwner;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\Subscription;
use App\Models\Payment;
use Illuminate\Http\Request;
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
     * Upgrade subscription plan.
     * If the plan is Free, upgrades immediately.
     * If it is a Paid plan, creates a Razorpay Subscription / Order.
     */
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

            $user->load(['activePlan', 'currentSubscription']);

            return response()->json([
                'message' => 'Subscription updated successfully to Free plan',
                'plan' => $user->activePlan,
                'subscription' => $user->currentSubscription,
                'user' => $user,
                'shop_count' => $user->shops()->count(),
            ]);
        }

        // For paid plans, initialize Razorpay checkout
        try {
            $keyId = config('services.razorpay.key_id');
            $keySecret = config('services.razorpay.key_secret');

            if (empty($keyId) || empty($keySecret)) {
                return response()->json(['message' => 'Razorpay payment gateway credentials not configured.'], 500);
            }

            $api = new RazorpayApi($keyId, $keySecret);

            // Dynamically create or retrieve plan on Razorpay
            $cacheKey = 'razorpay_plan_' . $plan->slug . '_' . (int)($plan->price) . '_' . $plan->billing_period;
            $razorpayPlanId = Cache::rememberForever($cacheKey, function () use ($api, $plan) {
                $razorpayPlan = $api->plan->create([
                    'period' => $plan->billing_period === 'yearly' ? 'yearly' : 'monthly',
                    'interval' => 1,
                    'item' => [
                        'name' => $plan->name,
                        'amount' => (int)($plan->price * 100), // in paise
                        'currency' => 'INR',
                        'description' => $plan->description ?? 'Subscription to ' . $plan->name
                    ]
                ]);
                return $razorpayPlan['id'];
            });

            // Create subscription on Razorpay
            $subscriptionData = [
                'plan_id' => $razorpayPlanId,
                'total_count' => $plan->billing_period === 'yearly' ? 5 : 60, // 5 years
                'quantity' => 1,
                'customer_notify' => 1,
                'notes' => [
                    'user_id' => $user->id,
                    'plan_slug' => $plan->slug,
                    'plan_id' => $plan->id
                ]
            ];

            $razorpaySubscription = $api->subscription->create($subscriptionData);

            return response()->json([
                'requires_payment' => true,
                'gateway' => 'razorpay',
                'key_id' => $keyId,
                'subscription_id' => $razorpaySubscription['id'],
                'plan' => $plan,
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'mobile' => $user->mobile,
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

