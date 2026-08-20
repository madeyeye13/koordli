<?php

namespace App\Livewire\Client;

use App\Models\Tenant\NotificationPreference;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.client')]
class NotificationPreferences extends Component
{
    use WithToast;

    public bool $emailEnabled = true;

    public function mount(): void
    {
        $pref = NotificationPreference::where('notifiable_type', 'client')
            ->where('notifiable_id', auth('client')->id())
            ->where('category', 'conversations')
            ->first();

        $this->emailEnabled = !$pref || in_array('email', $pref->channels ?? ['email']);
    }

    public function toggle(): void
    {
        $this->emailEnabled = !$this->emailEnabled;

        NotificationPreference::updateOrCreate(
            [
                'tenant_id'       => auth('client')->user()->tenant_id,
                'notifiable_type' => 'client',
                'notifiable_id'   => auth('client')->id(),
                'category'        => 'conversations',
            ],
            ['channels' => $this->emailEnabled ? ['email'] : []]
        );

        $this->toastSuccess('Preference saved.');
    }

    public function render()
    {
        return view('livewire.client.notification-preferences');
    }
}