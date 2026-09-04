<?php

namespace App\Listeners;

use App\Events\PlannerFeeFullyCollected;
use App\Models\Tenant\User;
use App\Services\Notifications\NotificationDispatchService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendFeeFullyCollectedNotification implements ShouldQueue
{
    public function handle(PlannerFeeFullyCollected $event): void
    {
        $budget = $event->budget;
        $eventModel = $budget->event;

        // is_system, never a hardcoded role name — same fix applied
        // consistently everywhere else in this app this session.
        $owners = User::withoutGlobalScope('tenant')
            ->where('tenant_id', $budget->tenant_id)
            ->whereHas('roles', fn($q) => $q->where('is_system', true))
            ->get();

        foreach ($owners as $owner) {
            app(NotificationDispatchService::class)->notify(
                notifiable: $owner,
                category: 'budget',
                notificationType: 'planner_fee_fully_collected',
                templateKey: 'planner_fee_fully_collected',
                placeholders: [
                    'user_name'  => $owner->name,
                    'event_name' => $eventModel?->name ?? 'the event',
                    'amount'     => number_format((float) $budget->fee_amount, 2),
                ],
                priority: 'normal',
                actionUrl: $eventModel ? route('tenant.events.budget', $eventModel->slug) : route('tenant.budget'),
                actionLabel: 'View Budget',
                subject: $budget,
                tenantId: $budget->tenant_id,
            );
        }
    }
}