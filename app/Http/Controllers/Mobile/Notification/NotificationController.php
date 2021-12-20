<?php

namespace App\Http\Controllers\Mobile\Notification;

use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    use ApiResponser;

    /**
     * Get notification,
     * filter by status = read or unread
     */
    public function notification(Request $request)
    {
        $attr = $request->validate([
            'status' => 'nullable|in:read,unread'
        ]);

        $customer = Auth::user();

        $notifications = $customer->notifications();

        // If there's no filter status
        if (count($attr) == 0) $notifications = $customer->notifications();

        // If there's filter status
        if (isset($attr['status']) && $attr['status'] == 'read') {
            $notifications = $customer->readNotifications();
        } else if (isset($attr['status']) && $attr['status'] == 'unread') {
            $notifications = $customer->unreadNotifications();
        }

        return $this->success($notifications->where('type', 'regexp', '\bNotifications\b')->get());
    }

    /**
     * Read all notifications
     */
    public function notificationReadAll()
    {
        $this->readAll('Notifications');

        return $this->success(null);
    }

    /**
     * Read specific notification / activity
     * based on id
     */
    public function read($id)
    {
        $customer = Auth::user();

        $customer->unreadNotifications()->where('id', $id)
            ->update(['read_at' => now()]);

        return $this->success(null);
    }

    /**
     * Get activities,
     * filter by status = read or unread
     */
    public function activity(Request $request)
    {
        $attr = $request->validate([
            'status' => 'nullable|in:read,unread'
        ]);

        $customer = Auth::user();

        $notifications = $customer->notifications();

        // If there's no filter status
        if (count($attr) == 0) $notifications = $customer->notifications();

        // If there's filter status
        if (isset($attr['status']) && $attr['status'] == 'read') {
            $notifications = $customer->readNotifications();
        } else if (isset($attr['status']) && $attr['status'] == 'unread') {
            $notifications = $customer->unreadNotifications();
        }

        return $this->success($notifications->where('type', 'regexp', '\bActivities\b')->get());
    }

    /**
     * Read all activities
     */
    public function activityReadAll()
    {
        $this->readAll('Activities');

        return $this->success(null);
    }

    /**
     * Helper function to read all notifications or activities
     */
    private function readAll($regex = 'Notifications'): void
    {
        Auth::user()->unreadNotifications()
            ->where('type', 'regexp', '\b' . $regex . '\b')
            ->update(['read_at' => now()]);
    }
}
