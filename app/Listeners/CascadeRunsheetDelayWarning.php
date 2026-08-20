<?php

namespace App\Listeners;

use App\Events\RunsheetItemDelayed;
use App\Models\Tenant\ActivityTimeline;
use App\Services\Notifications\NotificationDispatchService;
use Illuminate\Contracts\Queue\ShouldQueue;

class CascadeRunsheetDelayWarning implements ShouldQueue
{
    public function handle(RunsheetItemDelayed $event): void
    {
        $item = $event->item;

        ActivityTimeline::log($item, 'runsheet_item_delayed', '"' . $item->title . '" marked as delayed' . ($item->notes ? ': ' . $item->notes : ''));

        // Warn anyone whose item DEPENDS ON this delayed one — staff only for now
        // (vendors don't yet participate in the notification_preferences system)
        foreach ($item->dependents as $dependent) {
            if (!$dependent->assignedTo) continue;

            app(NotificationDispatchService::class)->notify(
                notifiable: $dependent->assignedTo,
                category: 'runsheets',
                notificationType: 'runsheet_dependency_delayed',
                templateKey: 'runsheet_dependency_delayed',
                placeholders: [
                    'user_name'  => $dependent->assignedTo->name,
                    'task_name'  => $dependent->title,
                    'event_name' => $item->title,
                ],
                priority: 'high',
                actionUrl: route('tenant.events.runsheet', $item->runsheet->event->slug),
                actionLabel: 'View Runsheet',
                subject: $dependent,
                tenantId: $item->tenant_id,
            );
        }
    }
}