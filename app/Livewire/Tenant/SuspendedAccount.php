<?php

namespace App\Livewire\Tenant;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.auth')]
class SuspendedAccount extends Component
{
    public function mount(): void
    {
        if (auth('web')->user()?->tenant?->status === 'active') {
            $this->redirectRoute('tenant.dashboard', navigate: true);
        }
    }

    public function render()
    {
        return view('livewire.tenant.suspended-account');
    }
}
