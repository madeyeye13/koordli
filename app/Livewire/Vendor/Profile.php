<?php

namespace App\Livewire\Vendor;

use App\Models\Central\VendorAccount;
use App\Models\Tenant\QuickAccessLink;
use App\Services\FeatureGateService;
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

    public bool $showQuickAccess = false;

    public function mount(): void
    {
        $vendor = auth('vendor')->user();
        $this->name          = $vendor->name;
        $this->phone         = $vendor->phone ?? '';
        $this->business_name = $vendor->business_name ?? '';

        $this->showQuickAccess = app(FeatureGateService::class)->canAccess($vendor->tenant, 'quick_access_links');
    }

    public function regenerateQuickAccess(): void
    {
        QuickAccessLink::regenerateFor(VendorAccount::class, auth('vendor')->id(), auth('vendor')->user()->tenant_id);
        $this->toastSuccess('Your link has been regenerated. The previous link no longer works.');
    }

    public function resetQuickAccessPin(): void
    {
        $link = QuickAccessLink::withoutGlobalScope('tenant')
            ->where('person_type', VendorAccount::class)
            ->where('person_id', auth('vendor')->id())
            ->first();

        if ($link) {
            $link->update(['pin_hash' => null]);
            $this->toastSuccess('Your PIN has been reset. You\'ll set a new one next time you use your link.');
        }
    }

    public string $current_password          = '';
    public string $new_password              = '';
    public string $new_password_confirmation = '';

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

        if (!\Illuminate\Support\Facades\Hash::check($this->current_password, auth('vendor')->user()->password)) {
            $this->addError('current_password', 'Current password is incorrect.');
            return;
        }

        auth('vendor')->user()->update(['password' => \Illuminate\Support\Facades\Hash::make($this->new_password)]);

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);
        $this->toastSuccess('Password changed successfully.');
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
        $quickAccessLink = null;
        $quickAccessUrl  = null;

        if ($this->showQuickAccess) {
            $quickAccessLink = QuickAccessLink::withoutGlobalScope('tenant')
                ->where('person_type', VendorAccount::class)
                ->where('person_id', auth('vendor')->id())
                ->first();

            if (!$quickAccessLink) {
                $quickAccessLink = QuickAccessLink::create([
                    'tenant_id'   => auth('vendor')->user()->tenant_id,
                    'person_type' => VendorAccount::class,
                    'person_id'   => auth('vendor')->id(),
                ]);
            }

            $quickAccessUrl = route('public.quick-access', $quickAccessLink->token);
        }

        return view('livewire.vendor.profile', compact('quickAccessLink', 'quickAccessUrl'));
    }
}