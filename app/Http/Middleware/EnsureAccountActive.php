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

        if ($user && ($user->isSuspended() || $user->isDeleted())) {
            $token = $user->currentAccessToken();
            if ($token instanceof \Laravel\Sanctum\PersonalAccessToken) {
                $token->delete();
            }

            return response()->json([
                'error' => $user->isDeleted() ? 'account_deleted' : 'account_suspended',
                'message' => $user->isDeleted()
                    ? 'Your account has been deleted. Please contact the administrator to restore your account.'
                    : 'Your account has been suspended.',
            ], 403);
        }

        // Active Session Tracking: If user accesses app/web today and hasn't logged today
        if ($user) {
            $lastLogin = $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at) : null;
            if (!$lastLogin || !$lastLogin->isToday()) {
                $isFirstEver = ($lastLogin === null);
                $user->last_login_at = \Carbon\Carbon::now();
                $user->saveQuietly();

                try {
                    \App\Services\AnalyticsService::track(
                        eventName: 'login_success',
                        userId: $user->id,
                        shopId: $request->header('X-Shop-Id'),
                        properties: [
                            'login_type' => 'active_session',
                            'is_first_login' => $isFirstEver,
                            'name' => $user->name,
                            'email' => $user->email,
                        ],
                        platform: $request->header('X-Platform') ?? 'web',
                        appVersion: $request->header('X-App-Version'),
                        ipAddress: $request->ip()
                    );
                } catch (\Throwable $e) {
                    // Suppress to not break main request
                }
            }
        }

        return $next($request);
    }
}
