<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;

/**
 * Class NotificationsController
 *
 * Controller for handling notification-related backend requests.
 */
class NotificationsController extends Controller
{
    /**
     * Get the authenticated user's notifications.
     *
     * @return array
     */
    public function getNotifications()
    {
        return [
            'read'      => auth()->user()->readNotifications,
            'unread'    => auth()->user()->unreadNotifications,
            'usertype'  => auth()->user()->roles->first()->name,
        ];
    }

    /**
     * Mark a specific notification as read.
     *
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    public function markAsRead(Request $request)
    {
        return auth()->user()->notifications->where('id', $request->id)->markAsRead();
    }

    /*
    // Uncomment the following method if you need to mark a notification as read and redirect the user.

    /**
     * Mark a specific notification as read and redirect the user.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    /*public function markAsReadAndRedirect($id)
    {
        $notification = auth()->user()->notifications->where('id', $id)->first();
        $notification->markAsRead();

        if (auth()->user()->roles->first()->name == 'user') {
            if ($notification->type == 'App\Notifications\NewCommentForPostOwnerNotify') {
                return redirect()->route('users.comment.edit', $notification->data['id']);
            } else {
                return redirect()->back();
            }
        }
    }*/
}
