<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Display a listing of the user's notifications
     */
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $query = Notification::forUser($userId);

        if ($request->boolean('unread_only')) {
            $query->unread();
        }

        $query->orderBy('created_at', 'desc');

        $perPage = $request->input('per_page', 15);
        $notifications = $query->paginate($perPage);

        $unreadCount = Notification::forUser($userId)->unread()->count();

        return response()->json([
            'success' => true,
            'data' => [
                'items' => NotificationResource::collection($notifications->items())->resolve(),
                'unread_count' => $unreadCount,
                'pagination' => [
                    'total' => $notifications->total(),
                    'per_page' => $notifications->perPage(),
                    'current_page' => $notifications->currentPage(),
                    'last_page' => $notifications->lastPage(),
                ],
            ],
        ]);
    }

    /**
     * Display the specified notification
     */
    public function show(Request $request, string $id)
    {
        $notification = Notification::findOrFail($id);

        if ($notification->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'この通知を閲覧する権限がありません',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => new NotificationResource($notification),
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, string $id)
    {
        $notification = Notification::findOrFail($id);

        if ($notification->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'この通知を操作する権限がありません',
            ], 403);
        }

        if ($notification->is_read) {
            return response()->json([
                'success' => true,
                'message' => '既に既読です',
            ]);
        }

        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => '通知を既読にしました',
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request)
    {
        $userId = $request->user()->id;

        Notification::forUser($userId)
            ->unread()
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => '全ての通知を既読にしました',
        ]);
    }
}
