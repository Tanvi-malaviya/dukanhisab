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
     */
    public function current(Request $request)
    {
        $user = $request->user();
        $user->load(['activePlan', 'currentSubscription']);

        return response()->json([
            'plan' => $user->activePlan,
            'subscription' => $user->currentSubscription,
            'shop_count' => $user->shops()->count(),
        ]);
    }
}
