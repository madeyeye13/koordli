<?php

namespace App\Listeners;

use App\Events\ContractFullySigned;
use App\Events\ContractSent;
use App\Models\Tenant\ActivityTimeline;
use App\Services\Notifications\NotificationDispatchService;
use Illuminate\Contracts\Queue\ShouldQueue;

class LogContractActivity implements ShouldQueue
{
    public function handleSent(ContractSent $event): void
    {
        ActivityTimeline::log($event->contract, 'contract_sent', 'Contract sent to ' . $event->contract->vendor->name);
    }

    public function handleFullySigned(ContractFullySigned $event): void
    {
        $contract = $event->contract;

        ActivityTimeline::log($contract, 'contract_fully_signed', 'Contract fully signed by both parties');

        if ($contract->createdBy) {
            app(NotificationDispatchService::class)->notify(
                notifiable: $contract->createdBy,
                category: 'contracts',
                notificationType: 'contract_fully_signed',
                templateKey: 'contract_fully_signed',
                placeholders: [
                    'user_name'   => $contract->createdBy->name,
                    'event_name'  => $contract->title,
                    'task_name'   => $contract->vendor->name,
                ],
                priority: 'normal',
                actionUrl: route('tenant.contracts.show', $contract->uuid),
                actionLabel: 'View Contract',
                subject: $contract,
                tenantId: $contract->tenant_id,
            );
        }
    }
}