<?php

namespace App\Livewire\Vendor;

use App\Models\Central\VendorAccount;
use App\Models\Tenant\QuickAccessLink;
use App\Services\FeatureGateService;
use App\Traits\WithToast;
use Livewire\Component;

class QuickAccessWidget extends Component
{
    use WithToast;

    public function regenerate(): void
    {
        QuickAccessLink::regenerateFor(VendorAccount::class, auth('vendor')->id(), auth('vendor')->user()->tenant_id);
        $this->toastSuccess('Your link has been regenerated.');
    }

    public function render()
    {
        $vendor  = auth('vendor')->user();
        $enabled = app(FeatureGateService::class)->canAccess($vendor->tenant, 'quick_access_links');

        if (!$enabled) {
            return view('livewire.vendor.quick-access-widget', ['enabled' => false]);
        }

        $link = QuickAccessLink::withoutGlobalScope('tenant')
            ->where('person_type', VendorAccount::class)
            ->where('person_id', $vendor->id)
            ->first();

        if (!$link) {
            $link = QuickAccessLink::create([
                'tenant_id'   => $vendor->tenant_id,
                'person_type' => VendorAccount::class,
                'person_id'   => $vendor->id,
            ]);
        }

        return view('livewire.vendor.quick-access-widget', [
            'enabled' => true,
            'link'    => $link,
            'url'     => route('public.quick-access', $link->token),
        ]);
    }
}