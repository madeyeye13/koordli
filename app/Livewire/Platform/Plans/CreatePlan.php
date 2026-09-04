<?php

namespace App\Livewire\Platform\Plans;

use App\Models\Central\FeatureFlag;
use App\Models\Central\Plan;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Renderless;
use Livewire\Component;

#[Layout('layouts.platform')]
class CreatePlan extends Component
{
    use WithToast;

    public ?int   $planId        = null;
    public string $name          = '';
    public string $slug          = '';
    public string $billing_cycle = 'monthly';
    public int    $trial_days    = 0;
    public bool   $is_active     = true;
    public bool   $is_contact_only = false;
    public string $monthly_price   = '';
    public string $annual_discount_percent = '';
    public array  $features      = [];

        // Limit fields
    public string $max_events     = '';
    public string $max_staff      = '';
    public string $max_storage_mb = '';
    public string $max_guests     = '';
    public string $max_moodboards = '';

    // Add feature modal
    public bool   $showAddFeature    = false;
    public string $new_feature_key   = '';
    public string $new_feature_label = '';
    public string $new_feature_desc  = '';

    public function mount(?int $planId = null): void
    {
        if ($planId) {
            $plan                 = Plan::findOrFail($planId);
            $this->planId         = $planId;
            $this->name           = $plan->name;
            $this->slug           = $plan->slug;
            $this->billing_cycle  = $plan->billing_cycle;
            $this->trial_days     = $plan->trial_days;
            $this->is_active      = $plan->is_active;
            $this->is_contact_only = $plan->is_contact_only;
            $this->annual_discount_percent = $plan->annual_discount_percent ? (string) $plan->annual_discount_percent : '';
            $baseCurrency          = \App\Models\Central\BillingSetting::get('base_currency', 'NGN');
            $monthlyPrice          = $plan->prices()->where('currency', $baseCurrency)->where('billing_cycle', 'monthly')->first();
            $this->monthly_price   = $monthlyPrice ? (string) $monthlyPrice->amount : '';
            $this->features        = $plan->features ?? [];
            $limits               = $plan->limits ?? [];
            $this->max_events     = $limits['max_events'] == -1 ? '' : ($limits['max_events'] ?? '');
            $this->max_staff      = $limits['max_staff'] == -1 ? '' : ($limits['max_staff'] ?? '');
            $this->max_storage_mb = $limits['max_storage_mb'] == -1 ? '' : ($limits['max_storage_mb'] ?? '');
            $this->max_guests     = $limits['max_guests'] == -1 ? '' : ($limits['max_guests'] ?? '');
            $this->max_moodboards = ($limits['max_moodboards'] ?? -1) == -1 ? '' : $limits['max_moodboards'];
        }
    }

    public function updatedName(): void
    {
        if (!$this->planId) {
            $this->slug = \Illuminate\Support\Str::slug($this->name);
        }
    }

    public function openAddFeature(): void
    {
        $this->showAddFeature    = true;
        $this->new_feature_key   = '';
        $this->new_feature_label = '';
        $this->new_feature_desc  = '';
    }

    public function closeAddFeature(): void
    {
        $this->showAddFeature = false;
    }

    public function saveFeatureFlag(): void
    {
        $this->validate([
            'new_feature_key'   => 'required|string|max:100|unique:feature_flags,key',
            'new_feature_label' => 'required|string|max:100',
        ], [
            'new_feature_key.unique' => 'A feature with this key already exists.',
        ]);

        FeatureFlag::create([
            'key'         => \Illuminate\Support\Str::slug($this->new_feature_key, '_'),
            'label'       => $this->new_feature_label,
            'description' => $this->new_feature_desc,
            'is_active'   => true,
        ]);

        $this->showAddFeature = false;
        $this->toastSuccess('Feature flag created successfully.');
    }

    #[Renderless]
    public function setFeature(string $key, string $value): void
    {
        $this->features[$key] = $value;
    }

        public function save(): void
    {
        $this->validate([
            'name'                     => 'required|string|max:100',
            'slug'                     => 'required|string|max:100',
            'billing_cycle'            => 'required|in:monthly,annual,lifetime,trial',
            'trial_days'               => 'required|integer|min:0',
            'is_contact_only'          => 'boolean',
            'monthly_price'            => $this->is_contact_only ? 'nullable' : 'required|numeric|min:0',
            'annual_discount_percent'  => 'nullable|numeric|min:0|max:100',
        ], [
            'monthly_price.required'   => 'Enter a monthly price, or switch this plan to Contact Us.',
        ]);

        $limits = [
            'max_events'     => $this->max_events === '' ? -1 : (int) $this->max_events,
            'max_staff'      => $this->max_staff === '' ? -1 : (int) $this->max_staff,
            'max_storage_mb' => $this->max_storage_mb === '' ? -1 : (int) $this->max_storage_mb,
            'max_guests'     => $this->max_guests === '' ? -1 : (int) $this->max_guests,
            'max_moodboards' => $this->max_moodboards === '' ? -1 : (int) $this->max_moodboards,
        ];

        $data = [
            'name'                    => $this->name,
            'slug'                    => $this->slug,
            'billing_cycle'           => $this->billing_cycle,
            'trial_days'              => $this->trial_days,
            'is_active'               => $this->is_active,
            'is_contact_only'         => $this->is_contact_only,
            'annual_discount_percent' => $this->annual_discount_percent === '' ? 0 : $this->annual_discount_percent,
            'features'                => $this->features,
            'limits'                  => $limits,
        ];

        if ($this->planId) {
            $plan = Plan::find($this->planId);
            $plan->update($data);
        } else {
            $plan = Plan::create($data);
        }

        $this->syncMonthlyPrice($plan);

        if ($this->planId) {
            $this->toastSuccess('Plan updated successfully.');
        } else {
            $this->toastSuccess('Plan created successfully.');
            $this->redirect(route('platform.plans'), navigate: true);
        }
    }

    private function syncMonthlyPrice(Plan $plan): void
    {
        $baseCurrency = \App\Models\Central\BillingSetting::get('base_currency', 'NGN');

        if (!$this->is_contact_only && $this->monthly_price !== '') {
            \App\Models\Central\PlanPrice::updateOrCreate(
                ['plan_id' => $plan->id, 'currency' => $baseCurrency, 'billing_cycle' => 'monthly'],
                ['amount' => (float) $this->monthly_price, 'is_active' => true]
            );
        } else {
            // Contact-only or price cleared — deactivate any existing price so it can't leak
            // through into the registration/landing pricing calculations.
            \App\Models\Central\PlanPrice::where('plan_id', $plan->id)
                ->where('currency', $baseCurrency)
                ->where('billing_cycle', 'monthly')
                ->update(['is_active' => false]);
        }
    }

    public function render()
    {
        return view('livewire.platform.plans.create-plan', [
            'featureFlags' => FeatureFlag::where('is_active', true)->get(),
        ]);
    }
}