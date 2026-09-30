<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Shop;
use Illuminate\Http\Request;
use App\Notifications\AdminBroadcastNotification;
use Illuminate\Support\Facades\DB;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Storage;

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
            'free' => User::whereNull('active_plan_id')->orWhereHas('activePlan', function($q) { $q->where('slug', 'free'); })->count(),
            'premium' => User::whereHas('activePlan', function($q) { $q->where('slug', '!=', 'free'); })->count(),
        ];

        return view('admin.notifications.index', compact('notifications', 'userCounts'));
    }

    public function send(Request $request)
    {
        $request->validate([
            'title'   => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'type'    => 'required|in:promotional,maintenance,new_feature',
            'target'  => 'required|in:all,free,premium',
            'image'   => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
        ]);

        $title   = $request->input('title');
        $message = $request->input('message');
        $type    = $request->input('type');
        $target  = $request->input('target');

        // Handle optional image upload
        $imageUrl = null;
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $path = $request->file('image')->store('notifications', 'public');
            $imageUrl = Storage::url($path);
        }

        $query = User::query();

        if ($target === 'free') {
            $query->where(function($q) {
                $q->whereNull('active_plan_id')->orWhereHas('activePlan', function($pq) {
                    $pq->where('slug', 'free');
                });
            });
        } elseif ($target === 'premium') {
            $query->whereHas('activePlan', function($pq) {
                $pq->where('slug', '!=', 'free');
            });
        }

        $usersCount = $query->count();
        if ($usersCount === 0) {
            return back()->with('error', 'No users found matching the selected target segment.');
        }

        // Chunk process notifications for scalability
        $query->chunk(100, function ($users) use ($title, $message, $type, $imageUrl) {
            foreach ($users as $user) {
                $user->notify(new AdminBroadcastNotification($title, $message, $type, $imageUrl));
            }
        });

        AuditLog::log("Dispatched broadcast notification: {$title} to target segment: {$target}", [
            'type'            => $type,
            'recipient_count' => $usersCount,
            'has_image'       => !is_null($imageUrl),
        ]);

        return back()->with('success', "Notification dispatched successfully to {$usersCount} users.");
    }
}
