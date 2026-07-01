<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with(['user', 'admin']);

        // Search action/details
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhere('user_agent', 'like', "%{$search}%");
            });
        }

        // Filter Actor
        if ($request->filled('actor_type')) {
            $actorType = $request->input('actor_type');
            if ($actorType === 'admin') {
                $query->whereNotNull('admin_id');
            } elseif ($actorType === 'user') {
                $query->whereNotNull('user_id');
            }
        }

        $logs = $query->latest()->paginate(20)->withQueryString();

        return view('admin.logs.index', compact('logs'));
    }
}
