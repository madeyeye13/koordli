<?php

namespace App\Livewire\Client;

use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Traits\WithToast;

#[Layout('layouts.client')]
class Profile extends Component
{
    use WithToast;

    public string $name  = '';
    public string $phone = '';

    public string $current_password          = '';
    public string $new_password              = '';
    public string $new_password_confirmation = '';

    public function mount(): void
    {
        $client = auth('client')->user();
        $this->name  = $client->name;
        $this->phone = $client->phone ?? '';
    }

    public function saveProfile(): void
    {
        $this->validate([
            'name'  => 'required|string|min:2|max:100',
            'phone' => 'nullable|string|max:20',
        ]);

        auth('client')->user()->update([
            'name'  => $this->name,
            'phone' => $this->phone,
        ]);

        $this->toastSuccess('Profile updated.');
    }

    public function changePassword(): void
    {
        $this->validate([
            'current_password' => 'required',
            'new_password'     => [
                'required',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            ],
        ], [
            'new_password.regex' => 'Password must contain uppercase, lowercase and a number.',
        ]);

        if (!Hash::check($this->current_password, auth('client')->user()->password)) {
            $this->addError('current_password', 'Current password is incorrect.');
            return;
        }

        auth('client')->user()->update(['password' => Hash::make($this->new_password)]);

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);
        $this->toastSuccess('Password changed successfully.');
    }

    public function render()
    {
        return view('livewire.client.profile');
    }
}