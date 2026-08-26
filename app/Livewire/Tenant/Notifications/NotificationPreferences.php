<?php

namespace App\Livewire\Tenant\Notifications;

use App\Models\Tenant\NotificationPreference;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.tenant')]
class NotificationPreferences extends Component
{
    use WithToast;

    public array $preferences = [];

    protected array $categories = [
        'tasks'         => 'Tasks',
        'runsheets'     => 'Runsheets',
        'vendors'       => 'Vendors',
        'contracts'     => 'Contracts',
        'invoices'      => 'Invoices',
        'bookings'      => 'Bookings & Consultations',
        'rsvp'          => 'RSVP',
        'support'       => 'Support',
        'finance'       => 'Finance',
        'documents'     => 'Media Library',
    ];

    public function mount(): void
    {
        $user = auth()->user();

        foreach ($this->categories as $key => $label) {
            $this->preferences[$key] = NotificationPreference::channelsFor($user, $key);
        }
    }

    public function toggleChannel(string $category, string $channel): void
    {
        $current = $this->preferences[$category] ?? [];

        if (in_array($channel, $current)) {
            $current = array_values(array_diff($current, [$channel]));
        } else {
            $current[] = $channel;
        }

        $this->preferences[$category] = $current;

        NotificationPreference::updateOrCreate(
            [
                'tenant_id'       => auth()->user()->tenant_id,
                'notifiable_type' => get_class(auth()->user()),
                'notifiable_id'   => auth()->id(),
                'category'        => $category,
            ],
            ['channels' => $current]
        );

        $this->toastSuccess('Preferences updated.');
    }

    public function render()
    {
        return view('livewire.tenant.notifications.notification-preferences', ['categories' => $this->categories]);
    }
}