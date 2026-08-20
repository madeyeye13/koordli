<?php

namespace App\Listeners;

use App\Events\BookingSubmitted;
use App\Models\Tenant\User;
use App\Services\Notifications\NotificationDispatchService;
use Illuminate\Contracts\Queue\ShouldQueue;

class LogBookingActivity implements ShouldQueue
{
    public function handle(BookingSubmitted $event): void
    {
        $owner = User::withoutGlobalScope('tenant')
            ->where('tenant_id', $event->tenantId)
            ->orderBy('id')
            ->first();

        if (!$owner) return;

        app(NotificationDispatchService::class)->notify(
            notifiable: $owner,
            category: 'bookings',
            notificationType: 'booking_submitted',
            templateKey: 'booking_submitted',
            placeholders: [
                'user_name'  => $owner->name,
                'task_name'  => $event->guestName,
                'event_name' => $event->formName,
            ],
            priority: 'high',
            actionUrl: route('tenant.forms.submissions', $event->submission->form_id),
            actionLabel: 'View Submission',
            tenantId: $event->tenantId,
        );
    }
}