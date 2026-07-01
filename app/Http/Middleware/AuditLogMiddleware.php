<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\AuditLog;

class AuditLogMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Log POST, PUT, PATCH, DELETE operations that were processed successfully
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE']) && $response->getStatusCode() < 400) {
            $path = $request->path();
            $action = 'Performed action ' . $request->method() . ' on path ' . $path;

            // Simplify to human-readable names where possible
            if (str_contains($path, 'admin/users')) {
                $action = 'User Directory Update (' . $request->method() . ')';
            } elseif (str_contains($path, 'admin/shops')) {
                $action = 'Shop Parameter Override (' . $request->method() . ')';
            } elseif (str_contains($path, 'admin/subscriptions')) {
                $action = 'Subscription Level Modification (' . $request->method() . ')';
            } elseif (str_contains($path, 'admin/payments')) {
                $action = 'Billing Ledger Adjustment (' . $request->method() . ')';
            } elseif (str_contains($path, 'admin/settings')) {
                $action = 'Global Core Settings Reconfiguration (' . $request->method() . ')';
            } elseif (str_contains($path, 'admin/ads')) {
                $action = 'Ad Campaign Properties Changed (' . $request->method() . ')';
            } elseif (str_contains($path, 'admin/notifications')) {
                $action = 'Dispatched Global System Broadcast (' . $request->method() . ')';
            } elseif (str_contains($path, 'admin/support')) {
                $action = 'Ticket Resolution or Reply Filed (' . $request->method() . ')';
            } elseif (str_contains($path, 'admin/backups')) {
                $action = 'Manual Backup Sequence Executed (' . $request->method() . ')';
            }

            // Sanitized payload mapping
            $payload = $request->except(['password', 'password_confirmation', '_token', 'screenshot', 'logo', 'watermark']);

            AuditLog::log($action, $payload);
        }

        return $response;
    }
}
