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
    public array  $features      = [];

    // Limit fields
    public string $max_events     = '';
    public string $max_staff      = '';
    public string $max_storage_mb = '';
    public string $max_guests     = '';

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
            $this->features       = $plan->features ?? [];
            $limits               = $plan->limits ?? [];
            $this->max_events     = $limits['max_events'] == -1 ? '' : ($limits['max_events'] ?? '');
            $this->max_staff      = $limits['max_staff'] == -1 ? '' : ($limits['max_staff'] ?? '');
            $this->max_storage_mb = $limits['max_storage_mb'] == -1 ? '' : ($limits['max_storage_mb'] ?? '');
            $this->max_guests     = $limits['max_guests'] == -1 ? '' : ($limits['max_guests'] ?? '');
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
            'name'          => 'required|string|max:100',
            'slug'          => 'required|string|max:100',
            'billing_cycle' => 'required|in:monthly,annual,lifetime,trial',
            'trial_days'    => 'required|integer|min:0',
        ]);

        $limits = [
            'max_events'     => $this->max_events === '' ? -1 : (int) $this->max_events,
            'max_staff'      => $this->max_staff === '' ? -1 : (int) $this->max_staff,
            'max_storage_mb' => $this->max_storage_mb === '' ? -1 : (int) $this->max_storage_mb,
            'max_guests'     => $this->max_guests === '' ? -1 : (int) $this->max_guests,
        ];

        $data = [
            'name'          => $this->name,
            'slug'          => $this->slug,
            'billing_cycle' => $this->billing_cycle,
            'trial_days'    => $this->trial_days,
            'is_active'     => $this->is_active,
            'features'      => $this->features,
            'limits'        => $limits,
        ];

        if ($this->planId) {
            Plan::find($this->planId)->update($data);
            $this->toastSuccess('Plan updated successfully.');
        } else {
            Plan::create($data);
            $this->toastSuccess('Plan created successfully.');
            $this->redirect(route('platform.plans'), navigate: true);
        }
    }

    public function render()
    {
        return view('livewire.platform.plans.create-plan', [
            'featureFlags' => FeatureFlag::where('is_active', true)->get(),
        ]);
    }
}