<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Shop;
use Illuminate\Http\Request;
use App\Notifications\AdminBroadcastNotification;
use Illuminate\Support\Facades\DB;
use App\Models\AuditLog;

class NotificationController extends Controller
{
    public function index()
    {
        // Query historical notification broadcasts from Laravel native table
        $notifications = DB::table('notifications')
            ->leftJoin('users', 'notifications.notifiable_id', '=', 'users.id')
            ->select('notifications.id', 'notifications.data', 'notifications.created_at', 'users.name as recipient_name')
            ->orderBy('notifications.created_at', 'desc')
            ->paginate(10);

        // Map data column for display
        $notifications->getCollection()->transform(function ($item) {
            $item->data = json_decode($item->data, true);
            return $item;
        });

        $userCounts = [
            'all' => User::count(),
            'free' => Shop::whereNull('active_plan_id')->orWhereHas('activePlan', function($q) { $q->where('slug', 'free'); })->count(),
            'premium' => Shop::whereHas('activePlan', function($q) { $q->where('slug', 'premium'); })->count(),
        ];

        return view('admin.notifications.index', compact('notifications', 'userCounts'));
    }

    public function send(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'type' => 'required|in:promotional,maintenance,new_feature',
            'target' => 'required|in:all,free,premium',
        ]);

        $title = $request->input('title');
        $message = $request->input('message');
        $type = $request->input('type');
        $target = $request->input('target');

        $query = User::query();

        if ($target === 'free') {
            $query->whereHas('shops', function($q) {
                $q->whereNull('active_plan_id')->orWhereHas('activePlan', function($pq) {
                    $pq->where('slug', 'free');
                });
            });
        } elseif ($target === 'premium') {
            $query->whereHas('shops', function($q) {
                $q->whereHas('activePlan', function($pq) {
                    $pq->where('slug', 'premium');
                });
            });
        }

        $usersCount = $query->count();
        if ($usersCount === 0) {
            return back()->with('error', 'No users found matching the selected target segment.');
        }

        // Chunk process notifications for scalability
        $query->chunk(100, function ($users) use ($title, $message, $type) {
            foreach ($users as $user) {
                $user->notify(new AdminBroadcastNotification($title, $message, $type));
            }
        });

        AuditLog::log("Dispatched broadcast notification: {$title} to target segment: {$target}", [
            'type' => $type,
            'recipient_count' => $usersCount
        ]);

        return back()->with('success', "Notification dispatched successfully to {$usersCount} users.");
    }
}
