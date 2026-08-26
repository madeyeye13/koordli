<?php

namespace App\Listeners;

use App\Events\TaskAssigned;
use App\Services\Notifications\NotificationDispatchService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendTaskAssignedNotification implements ShouldQueue
{
    public function handle(TaskAssigned $event): void
    {
        $task = $event->task;

        if ($task->vendor_account_id) {
            app(\App\Services\Notifications\VendorNotificationService::class)
                ->notifyTaskAssigned($task);
            return;
        }

        if (!$task->assignedTo) {
            return;
        }

        app(NotificationDispatchService::class)->notify(
            notifiable: $task->assignedTo,
            category: 'tasks',
            notificationType: 'task_assigned',
            templateKey: 'task_assigned',
            placeholders: [
                'user_name'  => $task->assignedTo->name,
                'task_name'  => $task->title,
                'event_name' => $task->event?->name ?? 'General',
                'due_date'   => $task->due_date?->format('D, d M Y') ?? 'No due date',
            ],
            priority: 'normal',
            actionUrl: route('tenant.tasks.edit', $task->id),
            actionLabel: 'View Task',
            subject: $task,
            tenantId: $task->tenant_id,
        );
    }
}