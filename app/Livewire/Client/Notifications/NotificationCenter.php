<?php

namespace App\Livewire\Client\Notifications;

use Livewire\Attributes\On;
use Livewire\Component;

class NotificationCenter extends Component
{
    public string $categoryFilter = '';

    public function markAsRead(string $id): void
    {
        auth('client')->user()->notifications()->where('id', $id)->first()?->markAsRead();
    }

    public function markAllRead(): void
    {
        auth('client')->user()->unreadNotifications->markAsRead();
    }

    #[On('notification-received')]
    public function refresh(): void
    {
        // no-op body — triggers a re-render, pulling fresh notifications from render() below
    }

    public function render()
    {
        $notifications = auth('client')->user()->notifications()
            ->when($this->categoryFilter, fn($q) => $q->whereJsonContains('data->category', $this->categoryFilter))
            ->limit(30)
            ->get();

        $unreadCount = auth('client')->user()->unreadNotifications()->count();

        return view('livewire.client.notifications.notification-center', compact('notifications', 'unreadCount'));
    }
}