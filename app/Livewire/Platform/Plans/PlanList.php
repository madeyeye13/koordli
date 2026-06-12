<?php

namespace App\Livewire\Platform\Plans;

use App\Models\Central\Plan;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.platform')]
class PlanList extends Component
{
    use WithToast;

    public function toggleActive(int $id): void
    {
        $plan = Plan::find($id);
        if ($plan) {
            $plan->update(['is_active' => !$plan->is_active]);
            $this->toastSuccess(
                $plan->is_active ? 'Plan deactivated.' : 'Plan activated.'
            );
        }
    }

    public function render()
    {
        return view('livewire.platform.plans.plan-list', [
            'plans' => Plan::orderBy('created_at')->get(),
        ]);
    }
}