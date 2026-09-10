<?php

namespace App\Livewire\Platform\Notifications;

use Livewire\Attributes\On;
use Livewire\Component;

class PlatformNotificationCenter extends Component
{
    public bool   $showPanel = false;
    public string $categoryFilter = '';

    public function togglePanel(): void
    {
        $this->showPanel = !$this->showPanel;
    }

    public function markAsRead(string $id): void
    {
        auth('platform')->user()->notifications()->where('id', $id)->first()?->markAsRead();
    }

    public function markAllRead(): void
    {
        auth('platform')->user()->unreadNotifications->markAsRead();
    }

    #[On('platform-notification-received')]
    public function refresh(): void
    {
        // no-op body — triggers a re-render, pulling fresh notifications from render() below
    }

    public function render()
    {
        $notifications = auth('platform')->user()->notifications()
            ->when($this->categoryFilter, fn($q) => $q->whereJsonContains('data->category', $this->categoryFilter))
            ->limit(30)
            ->get();

        $unreadCount = auth('platform')->user()->unreadNotifications()->count();

        return view('livewire.platform.notifications.platform-notification-center', compact('notifications', 'unreadCount'));
    }
}