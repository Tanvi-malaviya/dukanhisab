<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth('admin')->check()) {
            return redirect()->route('admin.login')->with('error', 'Please log in to access the Super Admin Panel.');
        }

        $admin = auth('admin')->user();
        if ($admin->status === 'suspended') {
            auth('admin')->logout();
            return redirect()->route('admin.login')->with('error', 'Your administrator account has been suspended.');
        }

        return $next($request);
    }
}
