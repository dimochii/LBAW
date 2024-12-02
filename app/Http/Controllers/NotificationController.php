<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function show()
    {
        $user = Auth::user();

        // Retrieve all notifications for the user, ordered by date
        $notifications = Notification::where('authenticated_user_id', $user->id)
            ->orderBy('notification_date', 'desc')
            ->get();

        // Optional: Separate read and unread notifications
        $unreadNotifications = $notifications->where('is_read', false);
        $readNotifications = $notifications->where('is_read', true);

        return view('pages.notifications', [
            'unreadNotifications' => $unreadNotifications,
            'readNotifications' => $readNotifications,
        ]);
    }
}
