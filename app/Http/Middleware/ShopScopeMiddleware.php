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
        // Store on request for later use in controllers
        $request->attributes->set('shop_id', (int) $shopId);
        // Also bind in the container for any class that may resolve it via app('shop_id')
        app()->instance('shop_id', (int) $shopId);
        return $next($request);
    }
}
