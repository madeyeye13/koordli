<?php

namespace App\Livewire\Tenant;

use App\Models\Tenant\QuickAccessLink;
use App\Models\Tenant\User;
use App\Services\FeatureGateService;
use App\Traits\WithToast;
use Livewire\Component;

class QuickAccessWidget extends Component
{
    use WithToast;

    public function regenerate(): void
    {
        QuickAccessLink::regenerateFor(User::class, auth()->id(), auth()->user()->tenant_id);
        $this->toastSuccess('Your link has been regenerated.');
    }

    public function render()
    {
        $enabled = app(FeatureGateService::class)->canAccess(auth()->user()->tenant, 'quick_access_links');

        if (!$enabled) {
            return view('livewire.tenant.quick-access-widget', ['enabled' => false]);
        }

        $link = QuickAccessLink::withoutGlobalScope('tenant')
            ->where('person_type', User::class)
            ->where('person_id', auth()->id())
            ->first();

        if (!$link) {
            $link = QuickAccessLink::create([
                'tenant_id'   => auth()->user()->tenant_id,
                'person_type' => User::class,
                'person_id'   => auth()->id(),
            ]);
        }

        return view('livewire.tenant.quick-access-widget', [
            'enabled' => true,
            'link'    => $link,
            'url'     => route('public.quick-access', $link->token),
        ]);
    }
}