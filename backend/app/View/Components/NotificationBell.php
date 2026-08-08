<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Models\Notification;

class NotificationBell extends Component
{
    public $notifications;
    public $unreadCount;

    public function __construct()
    {
        $this->notifications = auth()->check() 
            ? Notification::where('user_id', auth()->id())->orderBy('created_at', 'desc')->take(10)->get()
            : collect();
        $this->unreadCount = $this->notifications->where('is_read', false)->count();
    }

    public function render()
    {
        return view('components.notification-bell');
    }

    public function markAsRead($id)
    {
        $notification = Notification::where('user_id', auth()->id())->find($id);
        if ($notification) {
            $notification->update(['is_read' => true, 'read_at' => now()]);
        }
    }

    public function markAllAsRead()
    {
        Notification::where('user_id', auth()->id())->where('is_read', false)->update(['is_read' => true, 'read_at' => now()]);
    }
}
