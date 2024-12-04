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
    public function markAsRead($id)
{
    $notification = Notification::findOrFail($id);  // Find the notification by its ID
    $notification->is_read = true;  // Mark it as read
    $notification->save();  // Save the change

    return redirect()->back();  // Redirect the user back to the notifications page
}

public function showUpvotes()
{
    $notifications = Auth::user()->notifications()
        ->where('type', 'App\Notifications\UpvoteNotification')
        ->get();

    return view('notifications.tabs.upvotes', compact('notifications'));
}

public function showPosts()
{
    $notifications = Auth::user()->notifications()
        ->where('type', 'App\Notifications\PostNotification')
        ->get();

    return view('notifications.tabs.posts', compact('notifications'));
}

public function showComments()
{
    $notifications = Auth::user()->notifications()
        ->where('type', 'App\Notifications\CommentNotification')
        ->get();

    return view('notifications.tabs.comments', compact('notifications'));
}

public function showFollows()
{
    $notifications = Auth::user()->notifications()
        ->where('type', 'App\Notifications\FollowNotification')
        ->get();

    return view('notifications.tabs.follows', compact('notifications'));
}
}
