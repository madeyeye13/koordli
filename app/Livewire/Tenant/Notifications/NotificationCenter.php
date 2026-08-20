<?php

namespace App\Livewire\Tenant\Notifications;

use Livewire\Attributes\On;
use Livewire\Component;

class NotificationCenter extends Component
{
    public bool   $showPanel = false;
    public string $categoryFilter = '';

    public function togglePanel(): void
    {
        $this->showPanel = !$this->showPanel;
    }

    public function markAsRead(string $id): void
    {
        auth()->user()->notifications()->where('id', $id)->first()?->markAsRead();
    }

    public function markAllRead(): void
    {
        auth()->user()->unreadNotifications->markAsRead();
    }

    #[On('notification-received')]
    public function refresh(): void
    {
        // no-op body — triggers a re-render, pulling fresh notifications from render() below
    }

    public function render()
    {
        $notifications = auth()->user()->notifications()
            ->when($this->categoryFilter, fn($q) => $q->whereJsonContains('data->category', $this->categoryFilter))
            ->limit(30)
            ->get();

        $unreadCount = auth()->user()->unreadNotifications()->count();

        return view('livewire.tenant.notifications.notification-center', compact('notifications', 'unreadCount'));
    }
}