<?php

namespace App\Http\Controllers\Api\ShopOwner;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\SubscriptionPlan;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Razorpay\Api\Api as RazorpayApi;
use App\Http\Controllers\Api\ShopOwner\AddOnApiController;

class SubscriptionApiController extends Controller
{
    /**
     * All purchasable plans (Free/Monthly/Yearly), for the app's Premium
     * screen to render pricing and feature limits.
     */
    public function plans(Request $request)
    {
        $user = $request->user();
        $plans = SubscriptionPlan::where('status', 'active')
            ->orderBy('price')
            ->get();

        $user->load(['activePlan']);
        $hasLifetimeActive = ($user->activePlan && $user->activePlan->slug === 'business');
        $daysSinceRegistration = $user->created_at ? $user->created_at->diffInDays(now()) : 0;

        $plans = $plans->filter(function ($plan) use ($daysSinceRegistration, $hasLifetimeActive) {
            if ($plan->slug === 'business') {
                $isExpired = ($daysSinceRegistration >= 7) && !$hasLifetimeActive;
                // If the business plan offer is expired, hide it completely
                if ($isExpired) {
                    return false;
                }
                $plan->setAttribute('days_left', max(0, 7 - $daysSinceRegistration));
                $plan->setAttribute('is_expired', false);
            } else {
                $plan->setAttribute('days_left', null);
                $plan->setAttribute('is_expired', false);
            }
            return true;
        })->values();

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

        // Stop the recurring charge on Razorpay first; otherwise the customer keeps
        // getting billed every year even though they cancelled here.
        if ($subscription->razorpay_subscription_id && !$this->cancelRazorpaySubscription($subscription->razorpay_subscription_id)) {
            return response()->json([
                'message' => 'We could not cancel your subscription with the payment gateway. Please try again in a moment.',
            ], 502);
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

        if ($plan->slug === 'business') {
            $user->load(['activePlan']);
            $hasLifetimeActive = ($user->activePlan && $user->activePlan->slug === 'business');
            $daysSinceRegistration = $user->created_at->diffInDays(now());
            if ($daysSinceRegistration >= 7 && !$hasLifetimeActive) {
                return response()->json(['message' => 'Lifetime plan offer has expired.'], 403);
            }
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

        if ($plan->slug === 'business') {
            $user->load(['activePlan']);
            $hasLifetimeActive = ($user->activePlan && $user->activePlan->slug === 'business');
            $daysSinceRegistration = $user->created_at->diffInDays(now());
            if ($daysSinceRegistration >= 7 && !$hasLifetimeActive) {
                return response()->json(['message' => 'Lifetime plan offer has expired.'], 403);
            }
        }

        $keyId = config('services.razorpay.key');
        $keySecret = config('services.razorpay.secret');

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
                'message' => 'Subscription updated successfully to ' . $plan->name,
                'plan' => $user->activePlan,
                'subscription' => $user->currentSubscription,
                'user' => $user,
                'shop_count' => $user->shops()->count(),
            ]);
        }

        // Lifetime (one-time) plans never auto-renew — go straight to a one-time Order.
        if ($plan->billing_period !== 'yearly') {
            return $this->createRazorpayOneTimeOrder($user, $plan, $keyId, $keySecret);
        }

        // Yearly plans always auto-renew: create a recurring Razorpay Subscription,
        // falling back to a one-time Order only if that fails.
        try {
            if (empty($keyId) || empty($keySecret)) {
                throw new \Exception('Razorpay credentials not configured.');
            }

            $api = new RazorpayApi($keyId, $keySecret);

            // Dynamically create or retrieve plan on Razorpay
            $cacheKey = 'razorpay_plan_' . $plan->slug . '_' . (int)($plan->price) . '_' . $plan->billing_period . '_' . \App\Support\Billing::razorpayPeriod();
            $razorpayPlanId = Cache::rememberForever($cacheKey, function () use ($api, $plan) {
                $razorpayPlan = $api->plan->create([
                    'period' => \App\Support\Billing::razorpayPeriod(),
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

            // Create subscription on Razorpay. total_count is Razorpay's cap on the
            // number of yearly renewal cycles; 100 years is effectively indefinite
            // auto-renewal for a human-held account.
            $subscriptionData = [
                'plan_id' => $razorpayPlanId,
                'total_count' => 100,
                'quantity' => 1,
                'customer_notify' => 1,
                'notes' => [
                    'type' => 'subscription',
                    'user_id' => (string)$user->id,
                    'plan_slug' => $plan->slug,
                    'plan_id' => (string)$plan->id
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
                    'name' => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')),
                    'email' => $user->email,
                    'mobile' => $user->mobile,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Razorpay Subscription Creation Failed: ' . $e->getMessage());

            // Fallback: one-time Order checkout (subscription just won't auto-renew).
            return $this->createRazorpayOneTimeOrder($user, $plan, $keyId, $keySecret);
        }
    }

    private function createRazorpayOneTimeOrder($user, SubscriptionPlan $plan, ?string $keyId, ?string $keySecret)
    {
        try {
            if (empty($keyId) || empty($keySecret)) {
                // Test/Sandbox fallback if Razorpay keys are not configured
                $mockOrderId = 'order_mock_' . bin2hex(random_bytes(8));
                return response()->json([
                    'requires_payment' => true,
                    'gateway' => 'razorpay',
                    'key_id' => 'rzp_test_placeholder',
                    'order_id' => $mockOrderId,
                    'amount' => (int)($plan->price * 100),
                    'currency' => 'INR',
                    'is_test_mode' => true,
                    'plan' => $plan,
                    'user' => [
                        'name' => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')),
                        'email' => $user->email,
                        'mobile' => $user->mobile,
                    ]
                ]);
            }

            $api = new RazorpayApi($keyId, $keySecret);
            $orderData = [
                'receipt' => 'sub_rcpt_' . $user->id . '_' . time(),
                'amount' => (int)($plan->price * 100), // paise
                'currency' => 'INR',
                'notes' => [
                    'type' => 'subscription',
                    'user_id' => (string)$user->id,
                    'plan_slug' => $plan->slug,
                    'plan_id' => (string)$plan->id
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
                    'name' => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')),
                    'email' => $user->email,
                    'mobile' => $user->mobile,
                ]
            ]);
        } catch (\Exception $orderEx) {
            Log::error('Razorpay Order Creation Failed: ' . $orderEx->getMessage());
            return response()->json(['message' => 'Failed to initiate payment gateway: ' . $orderEx->getMessage()], 500);
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

        $keySecret = config('services.razorpay.secret');

        // Signature verification
        $verified = false;
        
        // If in mock mode (starts with order_mock_ or key/secret is empty), bypass verification
        $isMock = (empty($keySecret)) || 
                  (!empty($orderId) && str_starts_with($orderId, 'order_mock_')) ||
                  (!empty($paymentId) && str_starts_with($paymentId, 'pay_mock_'));

        if ($isMock) {
            $verified = true;
        } else {
            if (!empty($subscriptionId)) {
                $expectedSignature = hash_hmac('sha256', $paymentId . '|' . $subscriptionId, $keySecret);
                $verified = hash_equals($expectedSignature, $signature);
            } elseif (!empty($orderId)) {
                $expectedSignature = hash_hmac('sha256', $orderId . '|' . $paymentId, $keySecret);
                $verified = hash_equals($expectedSignature, $signature);
            }
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
            $endsAt = \App\Support\Billing::yearlyPeriodEnd();
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
                'razorpay_subscription_id' => $subscriptionId ?: null,
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
            'message' => 'Subscription updated successfully to ' . $plan->name,
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
        // config() (not env()) so this still works after `php artisan config:cache`.
        // Without a secret anyone could forge a "payment charged" call, so refuse.
        $webhookSecret = config('services.razorpay.webhook_secret');
        if (empty($webhookSecret)) {
            Log::error('Razorpay webhook rejected: RAZORPAY_WEBHOOK_SECRET is not set.');
            return response()->json(['message' => 'Webhook secret not configured'], 500);
        }
        $expectedSignature = hash_hmac('sha256', $request->getContent(), $webhookSecret);
        if (!hash_equals($expectedSignature, (string) $request->header('X-Razorpay-Signature'))) {
            return response()->json(['message' => 'Invalid webhook signature'], 400);
        }

        $event = $request->input('event');
        $payload = $request->input('payload');

        // Auto-renewal is over (user cancelled, renewal payment kept failing,
        // all cycles done, or paused). Access is NOT revoked here: the user keeps
        // what they already paid for until ends_at, and it just won't renew.
        if (in_array($event, ['subscription.cancelled', 'subscription.halted', 'subscription.completed', 'subscription.paused'])) {
            $subEntity = $payload['subscription']['entity'] ?? null;
            if ($subEntity) {
                if (($subEntity['notes']['type'] ?? 'subscription') === 'addon') {
                    \App\Models\UserAddOn::where('razorpay_subscription_id', $subEntity['id'])
                        ->update(['auto_renew' => false]);
                }
                Log::info("Webhook {$event}: Razorpay subscription {$subEntity['id']} will not renew; access continues until ends_at.");
            }
            return response()->json(['status' => 'success']);
        }

        if (in_array($event, ['subscription.charged', 'subscription.activated'])) {
            $subEntity = $payload['subscription']['entity'] ?? null;
            $paymentEntity = $payload['payment']['entity'] ?? null;

            if ($subEntity && ($subEntity['notes']['type'] ?? 'subscription') === 'addon') {
                app(AddOnApiController::class)->activateFromWebhook(
                    $subEntity['notes'] ?? [],
                    $subEntity['id'],
                    $paymentEntity['id'] ?? null
                );
                return response()->json(['status' => 'success']);
            }

            if ($subEntity) {
                $userId = $subEntity['notes']['user_id'] ?? null;
                $planSlug = $subEntity['notes']['plan_slug'] ?? null;
                $razorpaySubId = $subEntity['id'];
                $paymentId = $paymentEntity['id'] ?? $razorpaySubId;

                if ($userId && $planSlug) {
                    $user = \App\Models\User::find($userId);
                    $plan = SubscriptionPlan::where('slug', $planSlug)->first();

                    if ($user && $plan) {
                        // The user already cancelled this subscription, but Razorpay still charged.
                        // Do not re-activate the plan; make sure it is cancelled on Razorpay's side.
                        if (Subscription::where('razorpay_subscription_id', $razorpaySubId)->where('status', 'cancelled')->exists()) {
                            Log::warning("Webhook ignored: charge for already-cancelled subscription {$razorpaySubId} (user {$user->id}). Cancelling it on Razorpay.");
                            $this->cancelRazorpaySubscription($razorpaySubId);
                            return response()->json(['status' => 'ignored']);
                        }

                        // Renewal = user already has this plan active and this payment is new.
                        // The first charge (recorded by verifyPayment) must not email "renewed".
                        $isRenewal = $paymentEntity
                            && $user->subscriptions()->where('plan_id', $plan->id)->where('status', 'active')->exists()
                            && !Payment::where('transaction_id', $paymentId)->exists();

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
                            $endsAt = \App\Support\Billing::yearlyPeriodEnd();
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
                                'razorpay_subscription_id' => $razorpaySubId,
                            ]
                        );

                        // Record Payment (only when the event carries one; subscription.activated does not)
                        if ($paymentEntity) {
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
                        }

                        if ($isRenewal) {
                            \App\Support\BillingMail::send(
                                $user,
                                "Your {$plan->name} subscription has been renewed",
                                'Subscription auto-renewed',
                                "Your {$plan->name} subscription was renewed automatically and your payment was received. Thank you!",
                                [
                                    'Plan' => $plan->name,
                                    'Amount charged' => '₹' . number_format($plan->price, 2),
                                    'Valid until' => $endsAt ? $endsAt->format('d M Y') : 'Lifetime',
                                    'Auto-renewal' => 'On (renews again next year)',
                                ]
                            );
                        }

                        Log::info("Webhook processed: User {$user->id} upgraded to plan {$plan->name}");
                    }
                }
            }
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Cancel a recurring subscription on Razorpay. Returns true when it is (now) cancelled,
     * or when there is nothing to cancel (test/mock ids or no credentials).
     */
    private function cancelRazorpaySubscription(string $razorpaySubscriptionId): bool
    {
        $keyId = config('services.razorpay.key');
        $keySecret = config('services.razorpay.secret');

        if (empty($keyId) || empty($keySecret)
            || str_starts_with($razorpaySubscriptionId, 'sub_mock_')
            || str_starts_with($razorpaySubscriptionId, 'sub_sim_')) {
            return true;
        }

        try {
            $razorpaySubscription = (new RazorpayApi($keyId, $keySecret))->subscription->fetch($razorpaySubscriptionId);

            if (in_array($razorpaySubscription->status, ['cancelled', 'completed', 'expired'], true)) {
                return true;
            }

            $razorpaySubscription->cancel(['cancel_at_cycle_end' => 0]);
            return true;
        } catch (\Throwable $e) {
            Log::error("Razorpay subscription cancel failed ({$razorpaySubscriptionId}): " . $e->getMessage());
            return false;
        }
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

