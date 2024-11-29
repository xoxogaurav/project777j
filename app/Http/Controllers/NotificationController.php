<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use ApiResponse;

    public function index()
    {
        try {
            $notifications = Notification::where('user_id', auth()->id())
                ->orderByDesc('created_at')
                ->get();

            return $this->successResponse($notifications);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch notifications', 'FETCH_ERROR');
        }
    }

    public function markAsRead($id)
    {
        try {
            $notification = Notification::where('user_id', auth()->id())
                ->findOrFail($id);

            $notification->update(['is_read' => true]);

            return $this->successResponse(null, 'Notification marked as read');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to mark notification as read', 'UPDATE_ERROR');
        }
    }

    public function markAllAsRead()
    {
        try {
            Notification::where('user_id', auth()->id())
                ->where('is_read', false)
                ->update(['is_read' => true]);

            return $this->successResponse(null, 'All notifications marked as read');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to mark notifications as read', 'UPDATE_ERROR');
        }
    }
}