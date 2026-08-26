<?php

namespace App\Livewire\Vendor;

use App\Models\Central\VendorAccount;
use App\Models\Tenant\NotificationPreference;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.vendor')]
class NotificationPreferences extends Component
{
    use WithToast;

    public array $categories = [
        'conversations' => ['label' => 'Messages',          'desc' => 'When someone sends you a message.'],
        'tasks'         => ['label' => 'Tasks Assigned',    'desc' => 'When a task is assigned to you.'],
        'runsheets'     => ['label' => 'Runsheet Updates',  'desc' => 'Runsheet changes relevant to you.'],
        'bookings'      => ['label' => 'Event Bookings',    'desc' => 'When you\'re booked for an event, or a booking status changes.'],
        'contracts'     => ['label' => 'Contracts',         'desc' => 'When a contract is sent to you for signature.'],
    ];

    public array $emailEnabled = [];

    public function mount(): void
    {
        $vendor = auth('vendor')->user();

        foreach (array_keys($this->categories) as $category) {
            $pref = NotificationPreference::where('notifiable_type', VendorAccount::class)
                ->where('notifiable_id', $vendor->id)
                ->where('category', $category)
                ->first();

            $this->emailEnabled[$category] = !$pref || in_array('mail', $pref->channels ?? ['mail']);
        }
    }

    public function toggle(string $category): void
    {
        $vendor = auth('vendor')->user();
        $this->emailEnabled[$category] = !$this->emailEnabled[$category];

        $channels = $this->emailEnabled[$category] ? ['database', 'mail'] : ['database'];

        NotificationPreference::updateOrCreate(
            [
                'tenant_id'       => $vendor->tenant_id,
                'notifiable_type' => VendorAccount::class,
                'notifiable_id'   => $vendor->id,
                'category'        => $category,
            ],
            ['channels' => $channels]
        );

        $this->toastSuccess('Preference saved.');
    }

    public function render()
    {
        return view('livewire.vendor.notification-preferences');
    }
}