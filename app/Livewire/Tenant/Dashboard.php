<?php

namespace App\Livewire\Tenant;

use App\Helpers\CurrencyHelper;
use App\Models\Tenant\Event;
use App\Models\Tenant\Task;
use App\Models\Tenant\Vendor;
use App\Models\Tenant\Guest;
use App\Models\Tenant\Budget;
use App\Enums\TaskStatus;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.tenant')]
class Dashboard extends Component
{
    public function render()
    {
        $totalEvents  = Event::count();
        $totalTasks   = Task::count();
        $overdueTasks = Task::pending()->overdue()->count();
        $totalVendors = Vendor::where('is_active', true)->count();
        $totalGuests  = Guest::count();

        $recentEvents = Event::with(['eventType', 'status'])
            ->latest()->take(5)->get();

        $pendingTasks = Task::with('assignedTo')
            ->pending()
            ->orderBy('due_date')
            ->take(5)
            ->get();

        $allBudgets       = Budget::with(['items', 'clientPayments', 'event'])->get();
        $totalAgreed      = $allBudgets->sum(fn($b) => $b->agreedBudget());
        $totalCollected   = $allBudgets->sum(fn($b) => $b->totalClientPaid());
        $totalOutstanding = $allBudgets->sum(fn($b) => $b->clientOutstanding());
        $totalActual      = $allBudgets->sum(fn($b) => $b->totalActual());

        return view('livewire.tenant.dashboard', [
            'totalEvents'      => $totalEvents,
            'totalTasks'       => $totalTasks,
            'overdueTasks'     => $overdueTasks,
            'totalVendors'     => $totalVendors,
            'totalGuests'      => $totalGuests,
            'recentEvents'     => $recentEvents,
            'pendingTasks'     => $pendingTasks,
            'totalAgreed'      => $totalAgreed,
            'totalCollected'   => $totalCollected,
            'totalOutstanding' => $totalOutstanding,
            'totalActual'      => $totalActual,
            'symbol'           => CurrencyHelper::forTenant(),
        ]);
    }
}