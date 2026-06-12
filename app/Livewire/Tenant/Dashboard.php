<?php

namespace App\Livewire\Tenant;

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
        $tenantId = auth()->user()->tenant_id;

        $totalEvents    = Event::count();
        $totalTasks     = Task::count();
        $overdueTasks   = Task::where('status', TaskStatus::ToDo->value)
                              ->whereDate('due_date', '<', today())
                              ->whereNotNull('due_date')
                              ->count();
        $totalVendors   = Vendor::count();
        $totalGuests    = Guest::count();

        $recentEvents   = Event::with(['eventType', 'status'])
                              ->latest()
                              ->take(5)
                              ->get();

        $pendingTasks   = Task::with('assignedTo')
                              ->where('status', '!=', TaskStatus::Done->value)
                              ->orderBy('due_date')
                              ->take(5)
                              ->get();

        $totalBudget    = Budget::sum('total_amount');

        return view('livewire.tenant.dashboard', [
            'totalEvents'  => $totalEvents,
            'totalTasks'   => $totalTasks,
            'overdueTasks' => $overdueTasks,
            'totalVendors' => $totalVendors,
            'totalGuests'  => $totalGuests,
            'recentEvents' => $recentEvents,
            'pendingTasks' => $pendingTasks,
            'totalBudget'  => $totalBudget,
        ]);
    }
}