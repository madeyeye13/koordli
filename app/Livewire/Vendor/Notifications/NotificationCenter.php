<?php

namespace App\Livewire\Vendor\Notifications;

use Livewire\Attributes\On;
use Livewire\Component;

class NotificationCenter extends Component
{
    public string $categoryFilter = '';

    public function markAsRead(string $id): void
    {
        auth('vendor')->user()->notifications()->where('id', $id)->first()?->markAsRead();
    }

    public function markAllRead(): void
    {
        auth('vendor')->user()->unreadNotifications->markAsRead();
    }

    #[On('notification-received')]
    public function refresh(): void {}

    public function render()
    {
        $notifications = auth('vendor')->user()->notifications()
            ->when($this->categoryFilter, fn($q) => $q->whereJsonContains('data->category', $this->categoryFilter))
            ->limit(30)
            ->get();

        $unreadCount = auth('vendor')->user()->unreadNotifications()->count();

        return view('livewire.vendor.notifications.notification-center', compact('notifications', 'unreadCount'));
    }
}