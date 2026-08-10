<?php

namespace App\Http\Controllers;

use App\Models\NotificationRecord;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = NotificationRecord::where('user_id', auth()->id())
            ->latest()
            ->paginate(20);
        return view('notifications.index', compact('notifications'));
    }

public function markAsRead(NotificationRecord $notification)
    {
        $this->authorizeNotification($notification);
        $notification->update(['is_read' => true]);
        return back();
    }

    public function markAllRead()
    {
        NotificationRecord::where('user_id', auth()->id())->where('is_read', false)->update(['is_read' => true]);
        return back()->with('success', 'All notifications marked as read.');
    }

    protected function authorizeNotification(NotificationRecord $notification)
    {
        abort_unless($notification->user_id === auth()->id(), 403);
    }
}
