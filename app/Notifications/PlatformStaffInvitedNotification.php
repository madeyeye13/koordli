<?php
// app/Notifications/PlatformStaffInvitedNotification.php
namespace App\Notifications;

use App\Models\Central\PlatformUser;
use Illuminate\Notifications\Notification;

class PlatformStaffInvitedNotification extends Notification
{
    public function __construct(public string $invitedByName, public string $role) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'subject'    => 'Welcome to the platform team',
            'body'       => "{$this->invitedByName} invited you as " . str_replace('platform_', '', $this->role) . '.',
            'category'   => 'staff',
            'priority'   => 'normal',
            'action_url' => route('platform.staff'),
        ];
    }
}