<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ShopScopeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Extracts the X-Shop-ID header and stores it in the request attributes and app container.
     * If the header is missing or invalid, returns a 400 response.
     */
    public function handle(Request $request, Closure $next)
    {
        $shopId = $request->header('X-Shop-ID');
        if (!$shopId || !is_numeric($shopId)) {
            return response()->json(['error' => 'Missing or invalid X-Shop-ID header'], 400);
        }
        // Shops beyond the user's shop limit (expired Shop Add-on) are locked.
        $user = $request->user();
        // X-Shop-ID is client-supplied — it must be one of THIS user's own
        // shops. Without this check any authenticated account could read or
        // write another shop's data just by sending a different id (and a
        // client left holding a previous account's shop id would silently
        // write into that other shop).
        if ($user && !$user->shops()->whereKey((int) $shopId)->exists()) {
            return response()->json([
                'error' => 'shop_forbidden',
                'message' => 'This shop does not belong to your account.',
            ], 403);
        }
        if ($user && in_array((int) $shopId, $user->lockedShopIds(), true)) {
            return response()->json([
                'error' => 'shop_locked',
                'message' => 'This shop is locked because your Shop Add-on has expired. Renew the Shop Add-on to use it again.',
            ], 403);
        }
        // Store on request for later use in controllers
        $request->attributes->set('shop_id', (int) $shopId);
        // Also bind in the container for any class that may resolve it via app('shop_id')
        app()->instance('shop_id', (int) $shopId);
        return $next($request);
    }
}
