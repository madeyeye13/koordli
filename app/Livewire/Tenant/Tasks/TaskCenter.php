<?php

namespace App\Livewire\Tenant\Tasks;

use App\Enums\TaskStatus;
use App\Enums\TaskPriority;
use App\Models\Tenant\Event;
use App\Models\Tenant\Task;
use App\Models\Tenant\TenantTaskCategory;
use App\Models\Tenant\User;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Renderless;

#[Layout('layouts.tenant')]
class TaskCenter extends Component
{
    use WithPagination, WithToast;

    #[Url]
    public string $view = 'all';

    #[Url]
    public string $search = '';

    #[Url]
    public string $statusFilter = '';

    #[Url]
    public string $priorityFilter = '';

    #[Url]
    public string $eventFilter = '';

    #[Url]
    public string $assigneeFilter = '';

    public bool $showDeleteModal = false;
    public ?int $deleteId        = null;

    public function updatedSearch(): void         { $this->resetPage(); }
    public function updatedStatusFilter(): void   { $this->resetPage(); }
    public function updatedPriorityFilter(): void { $this->resetPage(); }
    public function updatedEventFilter(): void    { $this->resetPage(); }
    public function updatedAssigneeFilter(): void { $this->resetPage(); }

    public function setView(string $view): void
    {
        $this->view = $view;
        $this->resetPage();
    }

    public function markDone(int $id): void
    {
        $task = Task::find($id);
        if ($task) {
            $task->update(['status' => TaskStatus::Done->value]);
            $this->toastSuccess('Task marked as done.');
        }
    }

    #[Renderless]
public function updateStatus(int $id, string $status): void
{
    $task = Task::find($id);
    if ($task) {
        $task->update(['status' => $status]);
        $this->toastSuccess('Status updated.');
    }
}

    public function confirmDelete(int $id): void
    {
        $this->deleteId        = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        $task = Task::find($this->deleteId);
        if ($task) {
            $task->delete();
            $this->toastSuccess('Task deleted.');
        }
        $this->showDeleteModal = false;
        $this->deleteId        = null;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->deleteId        = null;
    }

    public function render()
    {
        $userId = auth()->id();

        $query = Task::with(['event', 'assignedTo', 'category'])
            ->when($this->search, fn($q) =>
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%')
            )
            ->when($this->statusFilter, fn($q) =>
                $q->where('status', $this->statusFilter)
            )
            ->when($this->priorityFilter, fn($q) =>
                $q->where('priority', $this->priorityFilter)
            )
            ->when($this->eventFilter, fn($q) =>
                $q->where('event_id', $this->eventFilter)
            )
            ->when($this->assigneeFilter, fn($q) =>
                $q->where('assigned_to', $this->assigneeFilter)
            );

        // View filters
        match($this->view) {
            'event'   => $query->eventTasks(),
            'company' => $query->companyTasks(),
            'mine'    => $query->forUser($userId),
            default   => null,
        };

        $tasks = $query
            ->orderByRaw("FIELD(status, 'in_progress', 'todo', 'blocked', 'done', 'cancelled')")
            ->orderByRaw("FIELD(priority, 'urgent', 'high', 'normal', 'low')")
            ->orderBy('due_date')
            ->paginate(20);

        return view('livewire.tenant.tasks.task-center', [
            'tasks'        => $tasks,
            'events'       => Event::orderBy('date')->get(['id', 'name', 'slug']),
            'users'        => User::where('is_active', true)->get(['id', 'name']),
            'categories'   => TenantTaskCategory::orderBy('sort_order')->get(),
            'statuses'     => TaskStatus::cases(),
            'priorities'   => TaskPriority::cases(),
            'allCount'     => Task::count(),
            'eventCount'   => Task::eventTasks()->count(),
            'companyCount' => Task::companyTasks()->count(),
            'mineCount'    => Task::forUser($userId)->count(),
            'overdueCount' => Task::overdue()->count(),
        ]);
    }
}