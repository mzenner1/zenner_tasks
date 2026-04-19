<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()
            ->notifications()
            ->paginate(20);

        // Mark all as read when viewing the page
        auth()->user()->unreadNotifications->markAsRead();

        return view('notifications.index', compact('notifications'));
    }

    public function markRead(string $id)
    {
        if ($id === 'all') {
            auth()->user()->unreadNotifications->markAsRead();
        } else {
            $notification = auth()->user()->notifications()->findOrFail($id);
            $notification->markAsRead();
        }

        return redirect()->route('notifications.index');
    }
}
