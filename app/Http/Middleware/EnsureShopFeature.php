<?php

namespace App\Http\Middleware;

use App\Models\Shop;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureShopFeature
{
    /**
     * Allow the request only when the admin has switched the given optional module on for the
     * current shop (see Shop::FEATURES). Must run after shop.scope, which resolves shop_id.
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $shop = Shop::find($request->attributes->get('shop_id'));

        if (!$shop || !$shop->hasFeature($feature)) {
            return response()->json([
                'error' => 'feature_disabled',
                'message' => 'This feature is not enabled for your shop. Please contact support to enable it.',
            ], 403);
        }

        return $next($request);
    }
}
