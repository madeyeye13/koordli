<?php

namespace App\Services\Notifications;

use App\Models\Central\Client;
use App\Models\Central\Tenant;
use App\Models\Tenant\ClientEventAccess;
use App\Models\Tenant\Task;

class ClientNotificationService
{
    public function __construct(private NotificationDispatchService $dispatcher) {}

    public function notifyMilestoneCompleted(Task $task): void
    {
        $client = $this->clientFor($task->event_id, $task->tenant_id);
        if (!$client) return;

        $tenant = Tenant::find($task->tenant_id);
        if (!$tenant->clientNotificationEnabled('event_milestones')) return;

        $this->dispatcher->notify(
            notifiable: $client,
            category: 'event_milestones',
            notificationType: 'task_milestone_completed',
            templateKey: 'client_task_milestone_completed',
            placeholders: [
                'user_name'  => $client->name,
                'task_name'  => $task->title,
                'event_name' => $task->event?->name ?? 'your event',
            ],
            priority: 'normal',
            actionUrl: route('client.dashboard'),
            actionLabel: 'View Event',
            subject: $task,
            tenantId: $task->tenant_id,
        );
    }

        public function notifyVendorBookingChanged(\App\Models\Tenant\VendorEventAssignment $assignment): void
    {
        if (!$assignment->is_client_visible) return;

        $client = $this->clientFor($assignment->event_id, $assignment->tenant_id);
        if (!$client) return;

        $tenant = Tenant::find($assignment->tenant_id);
        if (!$tenant->clientNotificationEnabled('vendor_bookings')) return;

        $this->dispatcher->notify(
            notifiable: $client,
            category: 'vendor_bookings',
            notificationType: 'vendor_booking_status_changed',
            templateKey: 'client_vendor_booking_status_changed',
            placeholders: [
                'user_name'   => $client->name,
                'vendor_name' => $assignment->vendor?->name ?? 'Vendor',
                'event_name'  => $assignment->event?->name ?? 'your event',
                'status'      => ucfirst($assignment->status),
            ],
            priority: 'normal',
            actionUrl: route('client.dashboard'),
            actionLabel: 'View Event',
            subject: $assignment,
            tenantId: $assignment->tenant_id,
        );
    }

    public function notifyPaymentRecorded(\App\Models\Tenant\ClientPayment $payment): void
    {
        $budget = $payment->budget;
        if (!$budget || !$budget->event_id) return;

        $client = $this->clientFor($budget->event_id, $payment->tenant_id);
        if (!$client) return;

        $tenant = Tenant::find($payment->tenant_id);
        if (!$tenant->clientNotificationEnabled('payments')) return;

        $this->dispatcher->notify(
            notifiable: $client,
            category: 'payments',
            notificationType: 'client_payment_recorded',
            templateKey: 'client_payment_recorded',
            placeholders: [
                'user_name'  => $client->name,
                'amount'     => number_format((float) $payment->amount, 2),
                'event_name' => $budget->event?->name ?? 'your event',
            ],
            priority: 'normal',
            actionUrl: route('client.dashboard'),
            actionLabel: 'View Event',
            subject: $payment,
            tenantId: $payment->tenant_id,
        );
    }

    public function notifyEventDetailsChanged(\App\Models\Tenant\Event $event, array $changedFields): void
    {
        $client = $this->clientFor($event->id, $event->tenant_id);
        if (!$client) return;

        $tenant = Tenant::find($event->tenant_id);
        if (!$tenant->clientNotificationEnabled('event_details')) return;

        $this->dispatcher->notify(
            notifiable: $client,
            category: 'event_details',
            notificationType: 'event_details_changed',
            templateKey: 'client_event_details_changed',
            placeholders: [
                'user_name'  => $client->name,
                'event_name' => $event->name,
                'changed'    => implode(', ', $changedFields),
            ],
            priority: 'normal',
            actionUrl: route('client.dashboard'),
            actionLabel: 'View Event',
            subject: $event,
            tenantId: $event->tenant_id,
        );
    }

    /**
     * Checks whether crossing this RSVP response has just passed a new
     * response-rate threshold (50/75/100%), and notifies the client if
     * so. Deliberately event-driven (checked on every submission) rather
     * than scheduled — unlike the deadline reminder, there's no natural
     * "offset" to poll for here, so checking at the moment a response
     * lands is both simpler and avoids the reminder engine's 15-minute
     * window edge cases.
     */
    public function notifyRsvpSubmitted(\App\Models\Tenant\RsvpResponse $response): void
    {
        $event = $response->event;
        if (!$event) return;

        $client = $this->clientFor($event->id, $response->tenant_id);
        if (!$client) return;

        $tenant = Tenant::find($response->tenant_id);
        if (!$tenant || !$tenant->clientNotificationEnabled('rsvp')) return;

        $this->dispatcher->notify(
            notifiable: $client,
            category: 'rsvp',
            notificationType: 'rsvp_submitted',
            templateKey: 'client_rsvp_submitted',
            placeholders: [
                'user_name'   => $client->name,
                'guest_name'  => $response->respondent_name,
                'event_name'  => $event->name ?? 'your event',
                'status'      => ucfirst($response->status),
            ],
            priority: 'normal',
            actionUrl: route('client.dashboard'),
            actionLabel: 'View Event',
            subject: $response,
            tenantId: $response->tenant_id,
        );
    }

    public function notifyWishSubmitted(\App\Models\Tenant\EventWish $wish): void
    {
        $event = $wish->event;
        if (!$event) return;

        $client = $this->clientFor($event->id, $wish->tenant_id);
        if (!$client) return;

        $tenant = Tenant::find($wish->tenant_id);
        if (!$tenant || !$tenant->clientNotificationEnabled('rsvp')) return;

        $this->dispatcher->notify(
            notifiable: $client,
            category: 'rsvp',
            notificationType: 'wish_submitted',
            templateKey: 'client_wish_submitted',
            placeholders: [
                'user_name'  => $client->name,
                'guest_name' => $wish->guest_name,
                'event_name' => $event->name ?? 'your event',
            ],
            priority: 'normal',
            actionUrl: route('client.dashboard'),
            actionLabel: 'View Event',
            subject: $wish,
            tenantId: $wish->tenant_id,
        );
    }

    public function checkRsvpMilestone(\App\Models\Tenant\RsvpForm $rsvpForm): void
    {
        if (!$rsvpForm->guest_limit || $rsvpForm->guest_limit <= 0) return;

        $client = $this->clientFor($rsvpForm->event_id, $rsvpForm->tenant_id);
        if (!$client) return;

        $tenant = Tenant::find($rsvpForm->tenant_id);
        if (!$tenant->clientNotificationEnabled('rsvp')) return;

        $confirmedCount = \App\Models\Tenant\RsvpResponse::where('rsvp_form_id', $rsvpForm->id)
            ->where('status', 'confirmed')
            ->get()
            ->sum(fn($r) => 1 + $r->plus_one_count);

        $percentage = (int) floor(($confirmedCount / $rsvpForm->guest_limit) * 100);

        $thresholds = [50, 75, 100];
        $alreadyNotified = $rsvpForm->milestones_notified ?? [];

        foreach ($thresholds as $threshold) {
            if ($percentage < $threshold) continue;
            if (in_array($threshold, $alreadyNotified)) continue;

            $this->dispatcher->notify(
                notifiable: $client,
                category: 'rsvp',
                notificationType: 'rsvp_milestone_reached',
                templateKey: 'client_rsvp_milestone_reached',
                placeholders: [
                    'user_name'  => $client->name,
                    'event_name' => $rsvpForm->event?->name ?? 'your event',
                    'percentage' => (string) $threshold,
                ],
                priority: 'normal',
                actionUrl: route('client.dashboard'),
                actionLabel: 'View Event',
                subject: $rsvpForm,
                tenantId: $rsvpForm->tenant_id,
            );

            $alreadyNotified[] = $threshold;
        }

        if (count($alreadyNotified) > count($rsvpForm->milestones_notified ?? [])) {
            // forceFill: RsvpForm.php's actual $fillable list is unconfirmed
            // from here, so this bypasses mass-assignment protection
            // entirely rather than risk a silent no-op if the column
            // isn't listed there yet.
            $rsvpForm->forceFill(['milestones_notified' => $alreadyNotified])->save();
        }
    }

    public function notifyMoodboardShared(\App\Models\Tenant\Moodboard $moodboard): void
    {
        $client = $this->clientFor($moodboard->event_id, $moodboard->tenant_id);
        if (!$client) return;

        $tenant = Tenant::find($moodboard->tenant_id);
        if (!$tenant->clientNotificationEnabled('moodboards')) return;

        $this->dispatcher->notify(
            notifiable: $client,
            category: 'moodboards',
            notificationType: 'moodboard_shared',
            templateKey: 'client_moodboard_shared',
            placeholders: [
                'user_name'   => $client->name,
                'board_title' => $moodboard->title,
                'event_name'  => $moodboard->event?->name ?? 'your event',
            ],
            priority: 'normal',
            actionUrl: route('client.dashboard'),
            actionLabel: 'View Moodboard',
            subject: $moodboard,
            tenantId: $moodboard->tenant_id,
        );
    }

    /**
     * Resolves "the client" for a given event — same simplification used
     * throughout the Vendor Selection feature (first ClientEventAccess
     * match). Kept here as one shared implementation rather than
     * duplicated per-hook.
     */
    private function clientFor(int $eventId, int $tenantId): ?Client
    {
        return ClientEventAccess::withoutGlobalScope('tenant')
            ->where('event_id', $eventId)
            ->where('tenant_id', $tenantId)
            ->with('client')
            ->first()?->client;
    }
}