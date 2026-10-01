<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Refuses every shop-owner API request from a suspended account and revokes the token used, so
 * the web panel and the app both sign out with the same message. (Suspending through the admin
 * panel already revokes all tokens — see User::booted(); this also covers a status changed any
 * other way.)
 */
class EnsureAccountActive
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->isSuspended()) {
            $token = $user->currentAccessToken();
            if ($token instanceof \Laravel\Sanctum\PersonalAccessToken) {
                $token->delete();
            }

            return response()->json([
                'error' => 'account_suspended',
                'message' => 'Your account has been suspended.',
            ], 403);
        }

        return $next($request);
    }
}
