<?php

namespace App\Http\Controllers\Api\ShopOwner;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

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
