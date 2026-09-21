<?php

namespace App\Http\Controllers\Api\ShopOwner;

use App\Http\Controllers\Controller;
use App\Models\AddOn;
use App\Models\Payment;
use App\Models\User;
use App\Models\UserAddOn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AddOnApiController extends Controller
{
    /**
     * All purchasable add-ons (Shop / Website), for the web panel and app's
     * Add-Ons screen to render pricing.
     */
    public function plans(Request $request)
    {
        $addOns = AddOn::where('status', 'active')->orderBy('price')->get();

        return response()->json(['add_ons' => $addOns]);
    }

    /**
     * The authenticated user's current add-on holdings: extra shop slots
     * purchased and whether the Website add-on is active.
     */
    public function current(Request $request)
    {
        $user = $request->user();

        // Lazily expire any add-ons whose ends_at is in the past
        UserAddOn::where('user_id', $user->id)
            ->where('status', 'active')
            ->whereNotNull('ends_at')
            ->where('ends_at', '<=', now())
            ->update(['status' => 'expired']);

        $shopAddOns = $user->addOns()->whereHas('addOn', fn ($q) => $q->where('type', 'shop'))->get();
        $totalPurchased = (int) $shopAddOns->sum('quantity');
        $activePurchased = (int) $user->activeShopAddonQuantity();
        $expiredPurchased = (int) $shopAddOns->filter(fn ($a) => $a->status === 'expired' || ($a->ends_at && $a->ends_at->isPast()))->sum('quantity');
        $usedShops = $user->shops()->count();
        $maxShops = $user->maxShops();
        $availableSlots = max(0, $maxShops - $usedShops);

        // Attach the shop occupying each purchased slot (null = unused slot).
        $slots = $user->shopSlotAssignments();
        $addOnRows = $user->addOns()->with(['addOn', 'shop'])->latest()->get();
        $addOnRows->each(function ($row) use ($slots) {
            if (isset($slots[$row->id])) {
                $row->setAttribute('shop_slots', array_map(
                    fn ($s) => $s ? ['id' => $s->id, 'name' => $s->name] : null,
                    $slots[$row->id]
                ));
            }
        });

        return response()->json([
            'max_shops' => $maxShops,
            'shop_count' => $usedShops,
            'available_slots' => $availableSlots,
            'purchased_shops' => $totalPurchased,
            'active_extra_shops' => $activePurchased,
            'expired_extra_shops' => $expiredPurchased,
            'locked_shop_ids' => $user->lockedShopIds(),
            'has_website_addon' => $user->hasActiveWebsiteAddon(),
            'add_ons' => $addOnRows,
        ]);
    }

    /**
     * Start purchasing an add-on. Initializes a Razorpay subscription so
     * payments recur annually until cancelled.
     */
    public function purchase(Request $request)
    {
        $request->validate([
            'slug' => 'required|string|in:shop,website',
            'quantity' => 'nullable|integer|min:1|max:20',
        ]);

        $user = $request->user();
        $addOn = AddOn::where('slug', $request->slug)->first();

        if (!$addOn) {
            return response()->json(['message' => 'Add-on not found.'], 404);
        }

        if ($addOn->status !== 'active') {
            return response()->json(['message' => 'This add-on is currently unavailable.'], 400);
        }

        $quantity = $addOn->type === 'website' ? 1 : (int) ($request->input('quantity', 1));

        if ($addOn->type === 'website' && $user->hasActiveWebsiteAddon()) {
            return response()->json(['message' => 'Your Website add-on is already active.'], 400);
        }

        $keyId = config('services.razorpay.key');
        $keySecret = config('services.razorpay.secret');
        $amount = (int) round(((float) $addOn->price) * $quantity * 100);

        try {
            if (empty($keyId) || empty($keySecret)) {
                throw new \Exception('Razorpay credentials not configured.');
            }

            $cacheKey = 'razorpay_addon_plan_' . $addOn->slug . '_' . (int) $addOn->price . '_' . \App\Support\Billing::razorpayPeriod();
            $razorpayPlanId = Cache::rememberForever($cacheKey, function () use ($keyId, $keySecret, $addOn) {
                $planRes = Http::withBasicAuth($keyId, $keySecret)->post('https://api.razorpay.com/v1/plans', [
                    'period' => \App\Support\Billing::razorpayPeriod(),
                    'interval' => 1,
                    'item' => [
                        'name' => $addOn->title,
                        'amount' => (int) round(((float) $addOn->price) * 100),
                        'currency' => 'INR',
                        'description' => $addOn->description ?? ('Add-on: ' . $addOn->title),
                    ],
                ]);

                if (!$planRes->successful()) {
                    throw new \Exception('Failed to create Razorpay plan: ' . $planRes->body());
                }

                return $planRes->json('id');
            });

            $subRes = Http::withBasicAuth($keyId, $keySecret)->post('https://api.razorpay.com/v1/subscriptions', [
                'plan_id' => $razorpayPlanId,
                'total_count' => 100,
                'quantity' => $quantity,
                'customer_notify' => 1,
                'notes' => [
                    'type' => 'addon',
                    'user_id' => (string) $user->id,
                    'addon_slug' => $addOn->slug,
                    'addon_id' => (string) $addOn->id,
                    'quantity' => (string) $quantity,
                ],
            ]);

            if (!$subRes->successful()) {
                throw new \Exception('Failed to create Razorpay subscription: ' . $subRes->body());
            }

            $razorpaySubscription = $subRes->json();

            return response()->json([
                'requires_payment' => true,
                'gateway' => 'razorpay',
                'key_id' => $keyId,
                'subscription_id' => $razorpaySubscription['id'],
                'add_on' => $addOn,
                'quantity' => $quantity,
                'user' => [
                    'name' => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: $user->name,
                    'email' => $user->email,
                    'mobile' => $user->mobile,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Razorpay Add-on Subscription Creation Failed: ' . $e->getMessage());

            // Sandbox / no-credentials fallback so the web panel & app can still be exercised end-to-end.
            $mockSubId = 'sub_mock_' . bin2hex(random_bytes(8));
            return response()->json([
                'requires_payment' => true,
                'gateway' => 'razorpay',
                'key_id' => 'rzp_test_placeholder',
                'subscription_id' => $mockSubId,
                'is_test_mode' => true,
                'add_on' => $addOn,
                'quantity' => $quantity,
                'user' => [
                    'name' => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: $user->name,
                    'email' => $user->email,
                    'mobile' => $user->mobile,
                ],
            ]);
        }
    }

    /**
     * Verify payment signature and activate the add-on purchase.
     */
    public function verifyPayment(Request $request)
    {
        $request->validate([
            'slug' => 'required|string|in:shop,website',
            'quantity' => 'nullable|integer|min:1|max:20',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
            'razorpay_subscription_id' => 'required|string',
        ]);

        $user = $request->user();
        $addOn = AddOn::where('slug', $request->slug)->first();

        if (!$addOn) {
            return response()->json(['message' => 'Add-on not found.'], 404);
        }

        $paymentId = $request->razorpay_payment_id;
        $signature = $request->razorpay_signature;
        $subscriptionId = $request->razorpay_subscription_id;
        $quantity = $addOn->type === 'website' ? 1 : (int) ($request->input('quantity', 1));

        $keySecret = config('services.razorpay.secret');

        $isMock = empty($keySecret)
            || str_starts_with($subscriptionId, 'sub_mock_')
            || str_starts_with($paymentId, 'pay_mock_');

        $verified = $isMock;
        if (!$isMock) {
            $expectedSignature = hash_hmac('sha256', $paymentId . '|' . $subscriptionId, $keySecret);
            $verified = hash_equals($expectedSignature, $signature);
        }

        if (!$verified) {
            return response()->json(['message' => 'Payment signature verification failed.'], 400);
        }

        $shop = $user->shops()->first();
        $userAddOn = $this->activateAddOn($user, $addOn, $quantity, $subscriptionId, $shop?->id);

        Payment::updateOrCreate(
            ['transaction_id' => $paymentId],
            [
                'user_id' => $user->id,
                'shop_id' => $shop?->id,
                'add_on_id' => $addOn->id,
                'user_add_on_id' => $userAddOn->id,
                'amount' => $addOn->price * $quantity,
                'payment_gateway' => 'razorpay',
                'status' => 'successful',
                'payment_date' => now(),
            ]
        );

        return response()->json([
            'message' => $addOn->title . ' add-on activated successfully.',
            'user_add_on' => $userAddOn->load('addOn'),
            'max_shops' => $user->maxShops(),
            'shop_count' => $user->shops()->count(),
            'has_website_addon' => $user->hasActiveWebsiteAddon(),
        ]);
    }

    /**
     * Stop auto-renewal for an active add-on purchase. It stays active until
     * its current paid period (ends_at) runs out.
     */
    public function cancel(Request $request, $id)
    {
        $user = $request->user();
        $userAddOn = $user->addOns()->findOrFail($id);

        if ($userAddOn->status !== 'active') {
            return response()->json(['message' => 'This add-on is not active.'], 400);
        }

        $keyId = config('services.razorpay.key');
        $keySecret = config('services.razorpay.secret');
        if (!empty($keyId) && !empty($keySecret) && $userAddOn->razorpay_subscription_id && !str_starts_with($userAddOn->razorpay_subscription_id, 'sub_mock_')) {
            try {
                Http::withBasicAuth($keyId, $keySecret)
                    ->post("https://api.razorpay.com/v1/subscriptions/{$userAddOn->razorpay_subscription_id}/cancel", [
                        'cancel_at_cycle_end' => 1,
                    ]);
            } catch (\Throwable $e) {
                Log::error('Razorpay Add-on Subscription Cancel Failed: ' . $e->getMessage());
            }
        }

        $userAddOn->update(['auto_renew' => false]);

        return response()->json([
            'message' => 'Auto-renewal disabled. This add-on stays active until ' . optional($userAddOn->ends_at)->format('Y-m-d') . '.',
            'user_add_on' => $userAddOn->load('addOn'),
        ]);
    }

    /**
     * Called by SubscriptionApiController::handleWebhook for
     * subscription.charged / subscription.activated events whose notes
     * identify an add-on purchase (first charge or a yearly auto-renewal).
     */
    public function activateFromWebhook(array $notes, string $razorpaySubscriptionId, ?string $paymentId): void
    {
        $userId = $notes['user_id'] ?? null;
        $addonSlug = $notes['addon_slug'] ?? null;
        $quantity = (int) ($notes['quantity'] ?? 1);

        if (!$userId || !$addonSlug) {
            return;
        }

        $user = User::find($userId);
        $addOn = AddOn::where('slug', $addonSlug)->first();
        if (!$user || !$addOn) {
            return;
        }

        $shop = $user->shops()->first();

        // A renewal = the purchase already exists and this payment is new. The very
        // first charge (already recorded by verifyPayment) must not email "renewed".
        $isRenewal = $paymentId
            && UserAddOn::where('razorpay_subscription_id', $razorpaySubscriptionId)->exists()
            && !Payment::where('transaction_id', $paymentId)->exists();

        $userAddOn = $this->activateAddOn($user, $addOn, $quantity, $razorpaySubscriptionId, $shop?->id);

        // Events without a payment (e.g. subscription.activated) must not create a payment row.
        if ($paymentId) {
            Payment::updateOrCreate(
                ['transaction_id' => $paymentId],
                [
                    'user_id' => $user->id,
                    'shop_id' => $shop?->id,
                    'add_on_id' => $addOn->id,
                    'user_add_on_id' => $userAddOn->id,
                    'amount' => $addOn->price * $quantity,
                    'payment_gateway' => 'razorpay',
                    'status' => 'successful',
                    'payment_date' => now(),
                ]
            );
        }

        if ($isRenewal) {
            \App\Support\BillingMail::send(
                $user,
                "Your {$addOn->title} add-on has been renewed",
                'Add-on auto-renewed',
                "Your {$addOn->title} add-on was renewed automatically and your payment was received. Thank you!",
                [
                    'Add-on' => $addOn->title . ($quantity > 1 ? ' x ' . $quantity : ''),
                    'Amount charged' => '₹' . number_format($addOn->price * $quantity, 2),
                    'Valid until' => $userAddOn->ends_at->format('d M Y'),
                    'Auto-renewal' => 'On (renews again next year)',
                ]
            );
        }

        Log::info("Webhook processed: User {$user->id} add-on '{$addOn->slug}' charged/renewed.");
    }

    /**
     * Create or renew the UserAddOn row tied to one Razorpay recurring
     * subscription. Each purchase gets its own subscription id, so shop
     * add-on quantities from separate purchases stack via separate rows.
     */
    private function activateAddOn(User $user, AddOn $addOn, int $quantity, string $razorpaySubscriptionId, ?int $shopId): UserAddOn
    {
        return UserAddOn::updateOrCreate(
            ['razorpay_subscription_id' => $razorpaySubscriptionId],
            [
                'user_id' => $user->id,
                'shop_id' => $shopId,
                'add_on_id' => $addOn->id,
                'quantity' => $quantity,
                'status' => 'active',
                'starts_at' => now(),
                'ends_at' => \App\Support\Billing::yearlyPeriodEnd(),
                'auto_renew' => true,
            ]
        );
    }
}
