<?php

namespace App\Livewire\Staff;

use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Traits\WithToast;

#[Layout('layouts.tenant')]
class Profile extends Component
{
    use WithToast;

    public string $name = '';

    public string $current_password          = '';
    public string $new_password              = '';
    public string $new_password_confirmation = '';

    public function mount(): void
    {
        $this->name = auth()->user()->name;
    }

    public function saveProfile(): void
    {
        $this->validate([
            'name' => 'required|string|min:2|max:100',
        ]);

        auth()->user()->update(['name' => $this->name]);
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

        if (!Hash::check($this->current_password, auth()->user()->password)) {
            $this->addError('current_password', 'Current password is incorrect.');
            return;
        }

        auth()->user()->update(['password' => Hash::make($this->new_password)]);

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);
        $this->toastSuccess('Password changed successfully.');
    }

    public function render()
    {
        return view('livewire.staff.profile');
    }
}