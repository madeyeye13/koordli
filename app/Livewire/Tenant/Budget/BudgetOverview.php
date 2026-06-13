<?php

namespace App\Livewire\Tenant\Budget;

use App\Helpers\CurrencyHelper;
use App\Models\Tenant\Budget;
use App\Models\Tenant\Event;
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
        $eventsWithBudget = Event::with(['budget.items', 'budget.clientPayments', 'status'])
            ->whereHas('budget')
            ->when($this->search, fn($q) =>
                $q->where('name', 'like', '%' . $this->search . '%')
            )
            ->orderBy('date', 'asc')
            ->get();

        $eventsWithoutBudget = Event::with('status')
            ->whereDoesntHave('budget')
            ->when($this->search, fn($q) =>
                $q->where('name', 'like', '%' . $this->search . '%')
            )
            ->orderBy('date', 'asc')
            ->get();

        $allBudgets       = Budget::with(['items', 'clientPayments', 'event'])->get();
        $totalAgreed      = $allBudgets->sum(fn($b) => $b->agreedBudget());
        $totalEstimated   = $allBudgets->sum(fn($b) => $b->totalEstimated());
        $totalActual      = $allBudgets->sum(fn($b) => $b->totalActual());
        $totalCollected   = $allBudgets->sum(fn($b) => $b->totalClientPaid());
        $totalOutstanding = $allBudgets->sum(fn($b) => $b->clientOutstanding());

        return view('livewire.tenant.budget.budget-overview', [
            'eventsWithBudget'    => $eventsWithBudget,
            'eventsWithoutBudget' => $eventsWithoutBudget,
            'totalAgreed'         => $totalAgreed,
            'totalEstimated'      => $totalEstimated,
            'totalActual'         => $totalActual,
            'totalCollected'      => $totalCollected,
            'totalOutstanding'    => $totalOutstanding,
            'symbol'              => CurrencyHelper::forTenant(),
        ]);
    }
}