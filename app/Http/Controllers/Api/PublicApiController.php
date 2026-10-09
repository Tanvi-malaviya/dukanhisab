<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\AddOn;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicApiController extends Controller
{
    /**
     * Return all active subscription plans for public display on the website.
     * No authentication required. CORS headers are set to allow the Next.js website.
     */
    public function plans(Request $request): JsonResponse
    {
        $plans = SubscriptionPlan::where('status', 'active')
            ->orderBy('price')
            ->get(['id', 'name', 'slug', 'description', 'price', 'billing_period', 'features']);

        return response()->json([
            'plans' => $plans,
        ])->header('Access-Control-Allow-Origin', '*')
          ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
          ->header('Access-Control-Allow-Headers', 'Content-Type, Accept');
    }

    /**
     * Return all active add-ons for public display on the website.
     */
    public function addons(Request $request): JsonResponse
    {
        $addons = AddOn::where('status', 'active')
            ->orderBy('id')
            ->get(['id', 'title', 'slug', 'type', 'description', 'price', 'billing_period', 'image']);

        return response()->json([
            'addons' => $addons,
        ])->header('Access-Control-Allow-Origin', '*')
          ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
          ->header('Access-Control-Allow-Headers', 'Content-Type, Accept');
    }
}
