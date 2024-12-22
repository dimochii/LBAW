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

        $notifications = Notification::where('authenticated_user_id', $user->id)
            ->orderBy('notification_date', 'desc')
            ->get();

        $unreadNotifications = $notifications->where('is_read', false);
        $readNotifications = $notifications->where('is_read', true);

        return view('pages.notifications', [
            'unreadNotifications' => $unreadNotifications,
            'readNotifications' => $readNotifications,
        ]);
    }

    public function markAsRead($id)
    {
        $notification = Notification::findOrFail($id);  // Find the notification by its ID
        $notification->is_read = true;  // Mark it as read
        $notification->save();  // Save the change
        return redirect()->back();  // Redirect the user back to the notifications page
    }


}
