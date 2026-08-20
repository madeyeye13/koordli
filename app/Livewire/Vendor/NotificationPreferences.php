<?php

namespace App\Livewire\Vendor;

use App\Models\Tenant\NotificationPreference;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.vendor')]
class NotificationPreferences extends Component
{
    use WithToast;

    public bool $emailEnabled = true;

    public function mount(): void
    {
        $pref = NotificationPreference::where('notifiable_type', 'vendor_account')
            ->where('notifiable_id', auth('vendor')->id())
            ->where('category', 'conversations')
            ->first();

        $this->emailEnabled = !$pref || in_array('email', $pref->channels ?? ['email']);
    }

    public function toggle(): void
    {
        $this->emailEnabled = !$this->emailEnabled;

        NotificationPreference::updateOrCreate(
            [
                'tenant_id'       => auth('vendor')->user()->tenant_id,
                'notifiable_type' => 'vendor_account',
                'notifiable_id'   => auth('vendor')->id(),
                'category'        => 'conversations',
            ],
            ['channels' => $this->emailEnabled ? ['email'] : []]
        );

        $this->toastSuccess('Preference saved.');
    }

    public function render()
    {
        return view('livewire.vendor.notification-preferences');
    }
}