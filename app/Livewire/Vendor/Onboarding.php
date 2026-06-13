<?php

namespace App\Livewire\Vendor;

use App\Traits\WithToast;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.vendor')]
class Onboarding extends Component
{
    use WithToast;

    public string $new_password              = '';
    public string $new_password_confirmation = '';
    public bool   $showNew                   = false;
    public bool   $showConfirm               = false;

    public function save(): void
    {
        $this->validate([
            'new_password' => [
                'required',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            ],
        ], [
            'new_password.regex' => 'Password must contain uppercase, lowercase and a number.',
        ]);

        $vendor = auth('vendor')->user();
        $vendor->update([
            'password'         => Hash::make($this->new_password),
            'password_changed' => true,
        ]);

        $this->redirect(route('vendor.dashboard'), navigate: true);
    }

    public function render()
    {
        return view('livewire.vendor.onboarding');
    }
}