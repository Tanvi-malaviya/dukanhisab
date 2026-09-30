<?php

namespace App\Http\Controllers\Api\ShopOwner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Read API for the notifications the admin's Broadcast Center dispatches (App\Notifications\
 * AdminBroadcastNotification, stored via Laravel's built-in `database` channel). Until this
 * existed, those rows were written but nothing ever read them back for the shop owner.
 */
class NotificationApiController extends Controller
{
    /**
     * List the authenticated user's own notifications, newest first.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $perPage = min((int) $request->input('per_page', 20), 50) ?: 20;
        $notifications = $user->notifications()->paginate($perPage);

        $items = $notifications->getCollection()->map(function ($n) {
            return [
                'id' => $n->id,
                'title' => $n->data['title'] ?? '',
                'message' => $n->data['message'] ?? '',
                'type' => $n->data['type'] ?? 'feature',
                'read' => $n->read_at !== null,
                'created_at' => $n->created_at,
            ];
        });

        return response()->json([
            'status' => true,
            'notifications' => $items,
            'data' => $items,
            'unread_count' => $user->unreadNotifications()->count(),
            'pagination' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'total' => $notifications->total(),
            ],
        ]);
    }

    /**
     * How many of the authenticated user's notifications are unread — cheap enough for a bell
     * badge to poll without pulling the whole list.
     */
    public function unreadCount(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'status' => true,
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }

    /**
     * Mark one of the authenticated user's own notifications as read.
     */
    public function markRead(Request $request, $id)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $notification = $user->notifications()->where('id', $id)->first();
        if (!$notification) {
            return response()->json(['status' => false, 'message' => 'Notification not found.'], 404);
        }

        if ($notification->read_at === null) {
            $notification->markAsRead();
        }

        return response()->json(['status' => true, 'message' => 'Marked as read.']);
    }

    /**
     * Mark every one of the authenticated user's notifications as read.
     */
    public function markAllRead(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $user->unreadNotifications()->update(['read_at' => now()]);

        return response()->json(['status' => true, 'message' => 'All notifications marked as read.']);
    }

    /**
     * Delete one of the authenticated user's own notifications.
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $notification = $user->notifications()->where('id', $id)->first();
        if (!$notification) {
            return response()->json(['status' => false, 'message' => 'Notification not found.'], 404);
        }

        $notification->delete();

        return response()->json(['status' => true, 'message' => 'Notification deleted.']);
    }
}
