<?php

namespace App\Livewire\Tenant\Tasks;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Tenant\Event;
use App\Models\Tenant\Task;
use App\Models\Tenant\TenantTaskCategory;
use App\Models\Tenant\User;
use App\Services\PermissionService;
use App\Traits\WithToast;
use Illuminate\Validation\Rule;
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
    public bool    $is_client_visible = false;

    public ?int    $taskId          = null;
    public ?Task   $task            = null;

    // Pre-select event if coming from event detail
    public ?int $preselectedEventId = null;

    public function mount(?int $id = null, ?int $eventId = null): void
    {
        $permission = $id ? 'tasks.edit' : 'tasks.create';

        abort_unless(
            app(PermissionService::class)->userCan(auth()->user(), $permission),
            403
        );

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
            $this->is_client_visible = (bool) $this->task->is_client_visible;
        }

        if ($eventId) {
            $this->event_id            = $eventId;
            $this->preselectedEventId  = $eventId;
        }
    }

    public function sendReminderNow(): void
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'tasks.edit')) {
            $this->toastError('You do not have permission to send task reminders.');
            return;
        }

        if (!$this->task || !$this->task->assignedTo) {
            $this->toastError('Assign this task to a staff member first.');
            return;
        }

        app(\App\Services\Notifications\NotificationDispatchService::class)->notify(
            notifiable: $this->task->assignedTo,
            category: 'tasks',
            notificationType: 'task_manual_reminder',
            templateKey: 'task_due_soon',
            placeholders: [
                'user_name'      => $this->task->assignedTo->name,
                'task_name'      => $this->task->title,
                'event_name'     => $this->task->event?->name ?? 'General',
                'due_date'       => $this->task->due_date?->format('D, d M Y') ?? 'No due date',
                'remaining_time' => 'as a manual reminder from your planner',
            ],
            priority: 'normal',
            actionUrl: route('tenant.tasks.edit', $this->task->id),
            actionLabel: 'View Task',
            subject: $this->task,
            tenantId: auth()->user()->tenant_id,
            isManual: true,
        );

        $this->toastSuccess('Reminder sent to ' . $this->task->assignedTo->name . '.');
    }

    public function save(): void
    {
        $permission = $this->task ? 'tasks.edit' : 'tasks.create';

        if (!app(PermissionService::class)->userCan(auth()->user(), $permission)) {
            $this->toastError('You do not have permission to ' . ($this->task ? 'edit this task.' : 'create tasks.'));
            return;
        }

        // Reassigning to a different person requires the assign permission
        // specifically, separate from general edit rights.
        $previousAssignee = $this->task?->assigned_to;
        if ($this->assigned_to && $this->assigned_to !== $previousAssignee) {
            if (!app(PermissionService::class)->userCan(auth()->user(), 'tasks.assign')) {
                $this->addError('assigned_to', 'You do not have permission to assign tasks.');
                return;
            }
        }

        $tenantId = auth()->user()->tenant_id;

        $this->validate([
            'title'            => 'required|string|min:2|max:300',
            'event_id'         => ['nullable', Rule::exists('events', 'id')->where('tenant_id', $tenantId)],
            'task_category_id' => ['nullable', Rule::exists('tenant_task_categories', 'id')->where('tenant_id', $tenantId)],
            'assigned_to'      => ['nullable', Rule::exists('users', 'id')->where('tenant_id', $tenantId)],
            'priority'         => 'required|in:low,normal,high,urgent',
            'status'           => 'required|in:todo,in_progress,blocked,done,cancelled',
            'due_date'         => 'nullable|date',
            'description'      => 'nullable|string|max:2000',
            'is_client_visible' => 'boolean',
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
            'is_client_visible' => $this->is_client_visible,
        ];

        if ($this->task) {
            $this->task->update($data);
            $this->toastSuccess('Task updated successfully.');

            if ($this->task->assigned_to && $this->task->assigned_to !== $previousAssignee) {
                event(new \App\Events\TaskAssigned($this->task));
            }
        } else {
            $newTask = Task::create($data);
            $this->toastSuccess('Task created successfully.');

            if ($newTask->assigned_to) {
                event(new \App\Events\TaskAssigned($newTask));
            }
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