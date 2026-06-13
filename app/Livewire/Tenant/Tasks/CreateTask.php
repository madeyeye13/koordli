<?php

namespace App\Livewire\Tenant\Tasks;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Tenant\Event;
use App\Models\Tenant\Task;
use App\Models\Tenant\TenantTaskCategory;
use App\Models\Tenant\User;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.tenant')]
class CreateTask extends Component
{
    use WithToast;

    public string  $title           = '';
    public ?int    $event_id        = null;
    public ?int    $task_category_id = null;
    public ?int    $assigned_to     = null;
    public string  $priority        = 'normal';
    public string  $status          = 'todo';
    public string  $due_date        = '';
    public string  $description     = '';

    public ?int    $taskId          = null;
    public ?Task   $task            = null;

    // Pre-select event if coming from event detail
    public ?int $preselectedEventId = null;

    public function mount(?int $id = null, ?int $eventId = null): void
    {
        if ($id) {
            $this->task             = Task::findOrFail($id);
            $this->taskId           = $id;
            $this->title            = $this->task->title;
            $this->event_id         = $this->task->event_id;
            $this->task_category_id = $this->task->task_category_id;
            $this->assigned_to      = $this->task->assigned_to;
            $this->priority         = $this->task->priority->value;
            $this->status           = $this->task->status->value;
            $this->due_date         = $this->task->due_date?->format('Y-m-d') ?? '';
            $this->description      = $this->task->description ?? '';
        }

        if ($eventId) {
            $this->event_id            = $eventId;
            $this->preselectedEventId  = $eventId;
        }
    }

    public function save(): void
    {
        $this->validate([
            'title'            => 'required|string|min:2|max:300',
            'event_id'         => 'nullable|exists:events,id',
            'task_category_id' => 'nullable|exists:tenant_task_categories,id',
            'assigned_to'      => 'nullable|exists:users,id',
            'priority'         => 'required|in:low,normal,high,urgent',
            'status'           => 'required|in:todo,in_progress,blocked,done,cancelled',
            'due_date'         => 'nullable|date',
            'description'      => 'nullable|string|max:2000',
        ]);

        $data = [
            'title'            => $this->title,
            'event_id'         => $this->event_id,
            'task_category_id' => $this->task_category_id,
            'assigned_to'      => $this->assigned_to,
            'priority'         => $this->priority,
            'status'           => $this->status,
            'due_date'         => $this->due_date ?: null,
            'description'      => $this->description ?: null,
        ];

        if ($this->task) {
            $this->task->update($data);
            $this->toastSuccess('Task updated successfully.');
        } else {
            Task::create($data);
            $this->toastSuccess('Task created successfully.');
        }

        // Redirect back to event detail if came from event, else task center
        if ($this->preselectedEventId || $this->event_id) {
            $event = Event::find($this->event_id ?? $this->preselectedEventId);
            if ($event) {
                $this->redirect(route('tenant.events.show', $event->slug), navigate: true);
                return;
            }
        }

        $this->redirect(route('tenant.tasks'), navigate: true);
    }

    public function render()
    {
        return view('livewire.tenant.tasks.create-task', [
            'events'     => Event::orderBy('date')->get(['id', 'name', 'slug']),
            'users'      => User::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'categories' => TenantTaskCategory::orderBy('sort_order')->get(),
            'statuses'   => TaskStatus::cases(),
            'priorities' => TaskPriority::cases(),
        ]);
    }
}