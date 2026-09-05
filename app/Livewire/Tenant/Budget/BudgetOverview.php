<?php

namespace App\Livewire\Tenant\Budget;

use App\Helpers\CurrencyHelper;
use App\Models\Tenant\Budget;
use App\Models\Tenant\Event;
use App\Services\PermissionService;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.tenant')]
class BudgetOverview extends Component
{
    use WithToast;

    #[Url]
    public string $search = '';

    public function render()
    {
        abort_unless(
            app(PermissionService::class)->userCan(auth()->user(), 'budget.view'),
            403
        );

        $tenantId = auth()->user()->tenant_id;
        $eventsWithBudget = Event::where('tenant_id', $tenantId)->with(['budget.items', 'budget.clientPayments', 'status'])
            ->whereHas('budget')
            ->when($this->search, fn($q) =>
                $q->where('name', 'like', '%' . $this->search . '%')
            )
            ->orderBy('date', 'asc')
            ->get();

        $eventsWithoutBudget = Event::where('tenant_id', $tenantId)->with('status')
            ->whereDoesntHave('budget')
            ->when($this->search, fn($q) =>
                $q->where('name', 'like', '%' . $this->search . '%')
            )
            ->orderBy('date', 'asc')
            ->get();

        $allBudgets       = Budget::where('tenant_id', $tenantId)->with(['items', 'clientPayments', 'event'])->get();
        $totalAgreed      = $allBudgets->sum(fn($b) => $b->agreedBudget());
        $totalEstimated   = $allBudgets->sum(fn($b) => $b->totalEstimated());
        $totalActual      = $allBudgets->sum(fn($b) => $b->totalActual());
        $totalCollected   = $allBudgets->sum(fn($b) => $b->totalClientPaid());
        $totalOutstanding = $allBudgets->sum(fn($b) => $b->clientOutstanding());
        $totalFeeRevenue  = $allBudgets->sum(fn($b) => $b->feeRevenueCollected());
        $totalBorneCost   = $allBudgets->sum(fn($b) => $b->plannerBorneCost());

        return view('livewire.tenant.budget.budget-overview', [
            'eventsWithBudget'    => $eventsWithBudget,
            'eventsWithoutBudget' => $eventsWithoutBudget,
            'totalAgreed'         => $totalAgreed,
            'totalEstimated'      => $totalEstimated,
            'totalActual'         => $totalActual,
            'totalCollected'      => $totalCollected,
            'totalOutstanding'    => $totalOutstanding,
            'totalFeeRevenue'     => $totalFeeRevenue,
            'totalNetPosition'    => $totalFeeRevenue - $totalBorneCost,
            'symbol'              => CurrencyHelper::forTenant(),
        ]);
    }
}