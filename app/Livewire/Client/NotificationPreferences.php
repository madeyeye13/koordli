<?php

namespace App\Livewire\Client;

use App\Models\Central\Client;
use App\Models\Tenant\NotificationPreference;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.client')]
class NotificationPreferences extends Component
{
    use WithToast;

    public array $categories = [
        'conversations'      => ['label' => 'Messages',              'desc' => 'When someone sends you a message.'],
        'event_milestones'   => ['label' => 'Event Progress',        'desc' => 'When a key task for your event is completed.'],
        'vendor_suggestions' => ['label' => 'Vendor Suggestions',    'desc' => 'When a vendor is suggested, or your own suggestion is decided.'],
        'vendor_bookings'    => ['label' => 'Vendor Confirmations',  'desc' => 'When a vendor\'s booking status changes.'],
        'payments'           => ['label' => 'Payment Confirmations','desc' => 'When a payment you made is recorded.'],
        'rsvp'               => ['label' => 'RSVP Updates',          'desc' => 'As your RSVP deadline approaches.'],
        'event_details'      => ['label' => 'Event Detail Changes',  'desc' => 'When your event\'s date, venue, or location changes.'],
        'moodboards'         => ['label' => 'Moodboards',            'desc' => 'When a new moodboard is shared with you.'],
    ];

    public array $emailEnabled = [];

    public function mount(): void
    {
        $client = auth('client')->user();

        foreach (array_keys($this->categories) as $category) {
            $pref = NotificationPreference::where('notifiable_type', Client::class)
                ->where('notifiable_id', $client->id)
                ->where('category', $category)
                ->first();

            $this->emailEnabled[$category] = !$pref || in_array('mail', $pref->channels ?? ['mail']);
        }
    }

    public function toggle(string $category): void
    {
        $client = auth('client')->user();
        $this->emailEnabled[$category] = !$this->emailEnabled[$category];

        // In-app (database) notifications always continue regardless of
        // this toggle — this page controls the EMAIL channel only. The
        // tenant-wide category switch (Client Vendor... / Client
        // Notifications settings) is the sole control for whether
        // anything fires at all.
        $channels = $this->emailEnabled[$category] ? ['database', 'mail'] : ['database'];

        NotificationPreference::updateOrCreate(
            [
                'tenant_id'       => $client->tenant_id,
                'notifiable_type' => Client::class,
                'notifiable_id'   => $client->id,
                'category'        => $category,
            ],
            ['channels' => $channels]
        );

        $this->toastSuccess('Preference saved.');
    }

    public function render()
    {
        return view('livewire.client.notification-preferences');
    }
}