<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\NewNotifications;


class NotificationsController extends Controller
{
    public function totalNotifications()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated'
            ], 401);
        }
        $notifications = $user->unreadNotifications;

        // Check if notifications are being fetched
        if ($notifications->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'No unread notifications',
                'notifications' => []
            ]);
        }
        return response()->json([
            'count' => $user->unreadNotifications->count()
        ]);
    }

    public function getUnreadNotifications()
    {
        try{
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'message' => 'Unauthenticated'
                ], 401);
            }
            $notifications = $user->unreadNotifications;

            // Check if notifications are being fetched
            if ($notifications->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No unread notifications',
                    'notifications' => []
                ]);
            }
    
            // Get unread notifications with pagination
            // $notifications = $user->unreadNotifications()->get();
            
            return response()->json([
                'success' => true,
                // 'user' => $user,
                'notifications' => $notifications,
                'count' => $user->unreadNotifications->count()
            ]);
       }catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch notifications',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function markAsRead($id)
    {
        $notification = auth()->user()
            ->notifications()
            ->findOrFail($id);

        $notification->markAsRead();

        return response()->json([
            'message' => 'Notification marked as read',
            'success' => true,
            'read' => $notification
        ]);
    }
}
