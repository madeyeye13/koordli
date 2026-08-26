<?php

namespace App\Livewire\Tenant;

use App\Enums\TaskStatus;
use App\Models\Tenant\ReminderLog;
use App\Models\Tenant\Runsheet;
use App\Models\Tenant\Task;
use App\Models\Tenant\VendorApplication;
use Livewire\Component;

class DashboardWidgets extends Component
{
    public function render()
    {
        $user = auth()->user();
        $tenantId = $user->tenant_id;

        $todaysTasks = Task::where('assigned_to', $user->id)
            ->whereNotIn('status', [TaskStatus::Done->value, TaskStatus::Cancelled->value])
            ->whereDate('due_date', today())
            ->orderBy('priority')
            ->limit(5)
            ->get();

        $overdueTasks = Task::where('assigned_to', $user->id)
            ->overdue()
            ->limit(5)
            ->get();

        $upcomingDeadlines = Task::where('assigned_to', $user->id)
            ->whereNotIn('status', [TaskStatus::Done->value, TaskStatus::Cancelled->value])
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [today()->addDay(), today()->addDays(7)])
            ->orderBy('due_date')
            ->limit(5)
            ->get();

        // "Escalated" = any task that has ever triggered an escalation-priority reminder log and is still pending
        // whereHas() cannot be reliably used on a MorphTo relationship
        // ('subject' can point to Task, VendorContract, RunsheetItem, etc.)
        // — Laravel's docs explicitly require whereHasMorph() here instead,
        // restricted to the specific class(es) intended. Using plain
        // whereHas() caused Eloquent to also try applying this status
        // filter against unrelated morphed types (e.g. QuickAccessLink),
        // which has no 'status' column at all, producing the SQL error.
        $escalatedTaskIds = ReminderLog::where('tenant_id', $tenantId)
            ->where('subject_type', Task::class)
            ->whereHasMorph('subject', [Task::class], function ($q) {
                $q->whereNotIn('status', [
                    \App\Enums\TaskStatus::Done->value,
                    \App\Enums\TaskStatus::Cancelled->value,
                ]);
            })
            ->distinct()
            ->pluck('subject_id');
        $escalatedTasks = Task::whereIn('id', $escalatedTaskIds)->limit(5)->get();

        $recentNotifications = $user->notifications()->limit(5)->get();

        $pendingVendorApprovals = VendorApplication::where('status', 'pending')->count();

        $todaysRunsheetItems = Runsheet::where('status', 'active')
            ->whereDate('date', today())
            ->with(['items' => fn($q) => $q->whereNotIn('status', ['done'])->orderBy('start_time')->limit(5)])
            ->get()
            ->pluck('items')
            ->flatten();

        return view('livewire.tenant.dashboard-widgets', compact(
            'todaysTasks', 'overdueTasks', 'upcomingDeadlines', 'escalatedTasks',
            'recentNotifications', 'pendingVendorApprovals', 'todaysRunsheetItems'
        ));
    }
}