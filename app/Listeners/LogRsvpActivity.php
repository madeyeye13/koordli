<?php

namespace App\Listeners;

use App\Events\RsvpSubmitted;
use App\Models\Tenant\User;
use App\Services\Notifications\NotificationDispatchService;
use Illuminate\Contracts\Queue\ShouldQueue;

class LogRsvpActivity implements ShouldQueue
{
    public function handle(RsvpSubmitted $event): void
    {
        $owner = User::withoutGlobalScope('tenant')
            ->where('tenant_id', $event->tenantId)
            ->orderBy('id')
            ->first();

        if (!$owner) return;

        app(NotificationDispatchService::class)->notify(
            notifiable: $owner,
            category: 'rsvp',
            notificationType: 'rsvp_submitted',
            templateKey: 'rsvp_submitted',
            placeholders: [
                'user_name'  => $owner->name,
                'task_name'  => $event->response->respondent_name ?? 'A guest',
                'event_name' => $event->response->rsvpForm->event->name ?? 'your event',
            ],
            priority: 'normal',
            tenantId: $event->tenantId,
        );
    }
}