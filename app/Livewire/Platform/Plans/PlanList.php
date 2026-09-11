<?php

namespace App\Livewire\Platform\Plans;

use App\Models\Central\Plan;
use App\Models\Central\PlatformSetting;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.platform')]
class PlanList extends Component
{
    use WithToast;

    public bool $showDeleteModal = false;
    public ?int $deleteId        = null;
    public string $deleteName    = '';
    public string $landingPricingMode = 'monthly';

    public function mount(): void
    {
        $this->landingPricingMode = PlatformSetting::get('landing_pricing_mode', 'monthly');
    }

    public function saveLandingPricingMode(): void
    {
        $this->validate([
            'landingPricingMode' => 'required|in:monthly,annual,both',
        ]);

        PlatformSetting::set('landing_pricing_mode', $this->landingPricingMode);
        $this->toastSuccess('Landing page pricing display updated.');
    }

    public function toggleActive(int $id): void
    {
        $plan = Plan::find($id);
        if (!$plan) return;

        $newState = !$plan->is_active;
        $plan->update(['is_active' => $newState]);
        $this->toastSuccess($newState ? 'Plan activated.' : 'Plan deactivated.');
    }

    public function setFeatured(int $id): void
    {
        // Unmark all others first
        Plan::where('id', '!=', $id)->update(['is_featured' => false]);
        $plan = Plan::find($id);
        if (!$plan) return;

        $wasFeatured = $plan->is_featured;
        $plan->update(['is_featured' => !$wasFeatured]);
        $this->toastSuccess($wasFeatured ? 'Plan unfeatured.' : 'Plan marked as Recommended.');
    }

    public function confirmDelete(int $id, string $name): void
    {
        $this->deleteId        = $id;
        $this->deleteName      = $name;
        $this->showDeleteModal = true;
    }

    public function deletePlan(): void
    {
        $plan = Plan::find($this->deleteId);

        if (!$plan) {
            $this->toastError('Plan not found.');
            $this->showDeleteModal = false;
            return;
        }

        // Check if any tenants are on this plan
        $tenantCount = \App\Models\Central\Tenant::where('plan_id', $this->deleteId)->count();
        if ($tenantCount > 0) {
            $this->toastError("Cannot delete — {$tenantCount} " . ($tenantCount === 1 ? 'company is' : 'companies are') . " on this plan.");
            $this->showDeleteModal = false;
            $this->deleteId        = null;
            return;
        }

        $plan->delete();
        $this->showDeleteModal = false;
        $this->deleteId        = null;
        $this->deleteName      = '';
        $this->toastSuccess('Plan deleted.');
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->deleteId        = null;
        $this->deleteName      = '';
    }

    public function render()
    {
        return view('livewire.platform.plans.plan-list', [
            'plans'       => Plan::with('prices')->orderBy('created_at')->get(),
            'baseCurrency'=> \App\Models\Central\BillingSetting::get('base_currency', 'NGN'),
        ]);
    }
}