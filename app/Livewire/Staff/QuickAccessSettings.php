<?php

namespace App\Livewire\Staff;

use App\Models\Tenant\QuickAccessLink;
use App\Models\Tenant\User;
use App\Services\FeatureGateService;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.tenant')]
class QuickAccessSettings extends Component
{
    use WithToast;

    public function mount(): void
    {
        abort_unless(
            app(FeatureGateService::class)->canAccess(auth()->user()->tenant, 'quick_access_links'),
            403
        );
    }

    public function regenerate(): void
    {
        QuickAccessLink::regenerateFor(User::class, auth()->id(), auth()->user()->tenant_id);
        $this->toastSuccess('Your link has been regenerated. The previous link no longer works.');
    }

    public function resetPin(): void
    {
        $link = QuickAccessLink::withoutGlobalScope('tenant')
            ->where('person_type', User::class)
            ->where('person_id', auth()->id())
            ->first();

        if ($link) {
            $link->update(['pin_hash' => null]);
            $this->toastSuccess('Your PIN has been reset. You\'ll set a new one next time you use your link.');
        }
    }

    public function render()
    {
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

        return view('livewire.staff.quick-access-settings', [
            'link' => $link,
            'url'  => route('public.quick-access', $link->token),
        ]);
    }
}