<?php

namespace App\Livewire\Tenant;

use App\Services\PermissionService;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.tenant')]
class ClientNotificationSettings extends Component
{
    use WithToast;

    public array $categories = [
        'event_milestones'  => ['label' => 'Event Progress', 'desc' => 'Notify when a staff-flagged milestone task is completed (e.g. "Venue Secured").'],
        'vendor_suggestions' => ['label' => 'Vendor Suggestions', 'desc' => 'Notify when a vendor is suggested, or when their own suggestion is decided.'],
        'vendor_bookings'   => ['label' => 'Vendor Confirmations', 'desc' => 'Notify when a visible vendor\'s booking status changes.'],
        'payments'          => ['label' => 'Payment Confirmations', 'desc' => 'Notify when a payment they made is recorded.'],
        'rsvp'              => ['label' => 'RSVP Updates', 'desc' => 'Notify as their RSVP deadline approaches.'],
        'event_details'     => ['label' => 'Event Detail Changes', 'desc' => 'Notify when the event date, venue, or location changes.'],
        'moodboards'        => ['label' => 'Moodboards', 'desc' => 'Notify when a new moodboard is shared with them.'],
    ];

    public array $settings = [];

    public function mount(): void
    {
        abort_unless(
            app(PermissionService::class)->userCan(auth()->user(), 'client-notifications.manage'),
            403
        );

        $stored = auth()->user()->tenant->client_notification_settings ?? [];
        foreach (array_keys($this->categories) as $key) {
            $this->settings[$key] = $stored[$key] ?? true;
        }
    }

    public function toggle(string $key): void
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'client-notifications.manage')) {
            $this->toastError('You do not have permission to manage this setting.');
            return;
        }

        $this->settings[$key] = !$this->settings[$key];

        auth()->user()->tenant->update([
            'client_notification_settings' => $this->settings,
        ]);
    }

    public function render()
    {
        return view('livewire.tenant.client-notification-settings');
    }
}