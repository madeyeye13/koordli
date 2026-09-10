<?php
// app/Notifications/PlatformRoleChangedNotification.php
namespace App\Notifications;

use Illuminate\Notifications\Notification;

class PlatformRoleChangedNotification extends Notification
{
    public function __construct(public string $newRole) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'subject'    => 'Your role has changed',
            'body'       => 'Your platform role is now ' . str_replace('platform_', '', $this->newRole) . '.',
            'category'   => 'staff',
            'priority'   => 'high',
            'action_url' => route('platform.dashboard'),
        ];
    }
}