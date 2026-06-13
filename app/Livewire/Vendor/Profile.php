<?php

namespace App\Livewire\Vendor;

use App\Models\Central\VendorAccount;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.vendor')]
class Profile extends Component
{
    use WithToast;

    public string $name          = '';
    public string $phone         = '';
    public string $business_name = '';

    public function mount(): void
    {
        $vendor = auth('vendor')->user();
        $this->name          = $vendor->name;
        $this->phone         = $vendor->phone ?? '';
        $this->business_name = $vendor->business_name ?? '';
    }

    public function save(): void
    {
        $this->validate([
            'name'          => 'required|string|min:2|max:100',
            'phone'         => 'nullable|string|max:20',
            'business_name' => 'nullable|string|max:150',
        ]);

        auth('vendor')->user()->update([
            'name'          => $this->name,
            'phone'         => $this->phone,
            'business_name' => $this->business_name,
        ]);

        $this->toastSuccess('Profile updated successfully.');
    }

    public function render()
    {
        return view('livewire.vendor.profile');
    }
}