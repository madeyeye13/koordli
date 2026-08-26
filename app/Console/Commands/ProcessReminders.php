<?php

namespace App\Console\Commands;

use App\Enums\TaskStatus;
use App\Models\Central\ReminderRule;
use App\Models\Central\Tenant;
use App\Models\Central\TenantNotificationSettings;
use App\Models\Tenant\ReminderLog;
use App\Models\Tenant\Task;
use App\Services\Notifications\NotificationDispatchService;
use Illuminate\Console\Command;

class ProcessReminders extends Command
{
    protected $signature   = 'koordli:process-reminders';
    protected $description = 'Scans all active reminder rules against live data and sends due reminders/escalations';

    public function handle(NotificationDispatchService $dispatcher): void
    {
        $rules = ReminderRule::where('is_active', true)->get();

        if ($rules->isEmpty()) {
            $this->info('No active reminder rules.');
            return;
        }

        Tenant::chunk(50, function ($tenants) use ($rules, $dispatcher) {
            foreach ($tenants as $tenant) {
                $settings = TenantNotificationSettings::forTenant($tenant->id);

                if (!$settings->reminders_enabled) continue;
                if (!$settings->isWithinBusinessHours()) continue;

                foreach ($rules as $rule) {
                    if ($rule->category === 'tasks') {
                        $this->processTaskRule($rule, $tenant->id, $settings, $dispatcher);
                    } elseif ($rule->category === 'contracts') {
                        $this->processContractRule($rule, $tenant->id, $settings, $dispatcher);
                    } elseif ($rule->category === 'invoices') {
                        $this->processInvoiceRule($rule, $tenant->id, $settings, $dispatcher);
                    } elseif ($rule->category === 'vendors') {
                        $this->processVendorApplicationRule($rule, $tenant->id, $settings, $dispatcher);
                    } elseif ($rule->category === 'runsheets') {
                        $this->processRunsheetRule($rule, $tenant->id, $dispatcher);
                    } elseif ($rule->category === 'rsvp') {
                        $this->processRsvpRule($rule, $tenant->id, $settings, $dispatcher);
                    }
                    // Remaining stage 5 modules (support, vendor applications) already added above
                }
            }
        });
    }
    

    private function processVendorApplicationRule($rule, int $tenantId, $settings, NotificationDispatchService $dispatcher): void
    {
        if ($settings->isWithinQuietHours() && $rule->priority !== 'critical') return;

        $applications = \App\Models\Tenant\VendorApplication::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->get();

        $owner = \App\Models\Tenant\User::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)->orderBy('id')->first();
        if (!$owner) return;

        foreach ($applications as $application) {
            foreach ($rule->offsets as $offsetMinutes) {
                $targetTime = $application->created_at->copy()->addMinutes($offsetMinutes);
                if (!now()->between($targetTime, $targetTime->copy()->addMinutes(15))) continue;

                $alreadySent = \App\Models\Tenant\ReminderLog::where('tenant_id', $tenantId)
                    ->where('reminder_rule_id', $rule->id)
                    ->where('subject_type', \App\Models\Tenant\VendorApplication::class)
                    ->where('subject_id', $application->id)->exists();
                if ($alreadySent) continue;

                $dispatcher->notify(
                    notifiable: $owner, category: 'vendors', notificationType: 'vendor_application_pending',
                    templateKey: $rule->template_key,
                    placeholders: ['user_name' => $owner->name, 'task_name' => $application->business_name],
                    priority: $rule->priority,
                    actionUrl: route('tenant.vendor.applications'), actionLabel: 'Review Application',
                    subject: $application, tenantId: $tenantId, reminderRuleId: $rule->id,
                );
            }
        }
    }

    private function processTaskRule(ReminderRule $rule, int $tenantId, TenantNotificationSettings $settings, NotificationDispatchService $dispatcher): void
    {
        if ($settings->isWithinQuietHours() && $rule->priority !== 'critical') {
            return;
        }

        if ($rule->notification_type === 'task_due_soon') {
            $this->processDueSoon($rule, $tenantId, $dispatcher);
        } elseif ($rule->notification_type === 'task_overdue') {
            $this->processOverdue($rule, $tenantId, $settings, $dispatcher);
        }
    }

    private function processContractRule($rule, int $tenantId, $settings, NotificationDispatchService $dispatcher): void
    {
        if ($settings->isWithinQuietHours() && $rule->priority !== 'critical') return;

        $contracts = \App\Models\Tenant\VendorContract::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('status', 'sent')
            ->whereNotNull('sent_at')
            ->with('vendor', 'createdBy')
            ->get();

        foreach ($contracts as $contract) {
            if (!$contract->createdBy) continue;

            foreach ($rule->offsets as $offsetMinutes) {
                $targetTime = $contract->sent_at->copy()->addMinutes($offsetMinutes);

                if (!now()->between($targetTime, $targetTime->copy()->addMinutes(15))) continue;

                $alreadySent = \App\Models\Tenant\ReminderLog::where('tenant_id', $tenantId)
                    ->where('reminder_rule_id', $rule->id)
                    ->where('subject_type', \App\Models\Tenant\VendorContract::class)
                    ->where('subject_id', $contract->id)
                    ->where('sent_at', '>=', $targetTime->copy()->subHours(1))
                    ->exists();

                if ($alreadySent) continue;

                $dispatcher->notify(
                    notifiable: $contract->createdBy,
                    category: 'contracts',
                    notificationType: 'contract_awaiting_signature',
                    templateKey: $rule->template_key,
                    placeholders: [
                        'user_name'  => $contract->createdBy->name,
                        'task_name'  => $contract->vendor->name,
                        'event_name' => $contract->title,
                    ],
                    priority: $rule->priority,
                    actionUrl: route('tenant.contracts.show', $contract->uuid),
                    actionLabel: 'View Contract',
                    subject: $contract,
                    tenantId: $tenantId,
                    reminderRuleId: $rule->id,
                );
            }
        }
    }

    private function processInvoiceRule($rule, int $tenantId, $settings, NotificationDispatchService $dispatcher): void
    {
        if ($settings->isWithinQuietHours() && $rule->priority !== 'critical') return;

        $invoices = \App\Models\Tenant\VendorInvoice::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->whereNotIn('status', ['draft', 'paid', 'cancelled'])
            ->whereNotNull('due_date')
            ->with('vendor', 'createdBy')
            ->get();

        $intervalMinutes = $rule->intervalMinutes() ?: 1440; // default daily for overdue

        foreach ($invoices as $invoice) {
            if (!$invoice->createdBy) continue;

            if ($rule->notification_type === 'invoice_due_soon') {
                foreach ($rule->offsets as $offsetMinutes) {
                    $targetTime = $invoice->due_date->copy()->addMinutes($offsetMinutes);
                    if (!now()->between($targetTime, $targetTime->copy()->addMinutes(15))) continue;

                    $alreadySent = \App\Models\Tenant\ReminderLog::where('tenant_id', $tenantId)
                        ->where('reminder_rule_id', $rule->id)
                        ->where('subject_type', \App\Models\Tenant\VendorInvoice::class)
                        ->where('subject_id', $invoice->id)
                        ->exists();
                    if ($alreadySent) continue;

                    $this->sendInvoiceNotification($dispatcher, $invoice, $rule, 'invoice_due_soon', $tenantId);
                }
            } elseif ($rule->notification_type === 'invoice_overdue' && $invoice->isOverdue()) {
                $lastSent = \App\Models\Tenant\ReminderLog::where('tenant_id', $tenantId)
                    ->where('reminder_rule_id', $rule->id)
                    ->where('subject_type', \App\Models\Tenant\VendorInvoice::class)
                    ->where('subject_id', $invoice->id)
                    ->latest('sent_at')->first();

                if ($lastSent && $lastSent->sent_at->diffInMinutes(now()) < $intervalMinutes) continue;

                $this->sendInvoiceNotification($dispatcher, $invoice, $rule, 'invoice_overdue', $tenantId);
            }
        }
    }

    private function sendInvoiceNotification(NotificationDispatchService $dispatcher, $invoice, $rule, string $type, int $tenantId): void
    {
        $dispatcher->notify(
            notifiable: $invoice->createdBy,
            category: 'invoices',
            notificationType: $type,
            templateKey: $rule->template_key,
            placeholders: [
                'user_name'  => $invoice->createdBy->name,
                'task_name'  => $invoice->vendor->name,
                'event_name' => $invoice->invoice_number,
                'due_date'   => $invoice->due_date->format('D, d M Y'),
            ],
            priority: $rule->priority,
            actionUrl: route('tenant.invoices.show', $invoice->uuid),
            actionLabel: 'View Invoice',
            subject: $invoice,
            tenantId: $tenantId,
            reminderRuleId: $rule->id,
        );
    }

    private function processDueSoon(ReminderRule $rule, int $tenantId, NotificationDispatchService $dispatcher): void
    {
        $tasks = Task::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->pending()
            ->whereNotNull('due_date')
            ->whereNotNull('assigned_to')
            ->with('assignedTo', 'event')
            ->get();

        foreach ($tasks as $task) {
            foreach ($rule->offsets as $offsetMinutes) {
                $targetTime = $task->due_date->copy()->addMinutes($offsetMinutes);

                // Fire only within a 15-minute window around the target time, and only once per offset
                if (!now()->between($targetTime, $targetTime->copy()->addMinutes(15))) continue;

                $alreadySent = ReminderLog::where('tenant_id', $tenantId)
                    ->where('reminder_rule_id', $rule->id)
                    ->where('subject_type', Task::class)
                    ->where('subject_id', $task->id)
                    ->where('sent_at', '>=', $task->due_date->copy()->addMinutes($offsetMinutes)->subHours(1))
                    ->exists();

                if ($alreadySent) continue;

                $remaining = $offsetMinutes <= -1440 ? '24 hours' : ($offsetMinutes <= -120 ? '2 hours' : 'soon');

                $dispatcher->notify(
                    notifiable: $task->assignedTo,
                    category: 'tasks',
                    notificationType: 'task_due_soon',
                    templateKey: $rule->template_key,
                    placeholders: [
                        'user_name'      => $task->assignedTo->name,
                        'task_name'      => $task->title,
                        'event_name'     => $task->event?->name ?? 'General',
                        'due_date'       => $task->due_date->format('D, d M Y'),
                        'remaining_time' => $remaining,
                    ],
                    priority: $rule->priority,
                    actionUrl: route('tenant.tasks.edit', $task->id),
                    actionLabel: 'View Task',
                    subject: $task,
                    tenantId: $tenantId,
                    reminderRuleId: $rule->id,
                );
            }
        }
    }

    private function processOverdue(ReminderRule $rule, int $tenantId, TenantNotificationSettings $settings, NotificationDispatchService $dispatcher): void
    {
        $tasks = Task::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->overdue()
            ->whereNotNull('assigned_to')
            ->with('assignedTo', 'event', 'createdBy')
            ->get();

        $intervalMinutes = $rule->intervalMinutes() ?: 360; // default every_6h

        foreach ($tasks as $task) {
            $lastSent = ReminderLog::where('tenant_id', $tenantId)
                ->where('reminder_rule_id', $rule->id)
                ->where('subject_type', Task::class)
                ->where('subject_id', $task->id)
                ->latest('sent_at')
                ->first();

            if ($lastSent && $lastSent->sent_at->diffInMinutes(now()) < $intervalMinutes) {
                continue; // not due for another reminder yet
            }

            $dispatcher->notify(
                notifiable: $task->assignedTo,
                category: 'tasks',
                notificationType: 'task_overdue',
                templateKey: $rule->template_key,
                placeholders: [
                    'user_name'  => $task->assignedTo->name,
                    'task_name'  => $task->title,
                    'event_name' => $task->event?->name ?? 'General',
                    'due_date'   => $task->due_date->format('D, d M Y'),
                ],
                priority: $rule->priority,
                actionUrl: route('tenant.tasks.edit', $task->id),
                actionLabel: 'View Task',
                subject: $task,
                tenantId: $tenantId,
                reminderRuleId: $rule->id,
            );

            // Escalation: if overdue longer than escalate_after_hours AND not yet escalated, also notify the tenant owner
            if ($rule->escalate_after_hours && $task->due_date->diffInHours(now()) >= $rule->escalate_after_hours) {
                $alreadyEscalated = ReminderLog::where('tenant_id', $tenantId)
                    ->where('subject_type', Task::class)
                    ->where('subject_id', $task->id)
                    ->where('notifiable_id', '!=', $task->assigned_to)
                    ->exists();

                if (!$alreadyEscalated && $task->createdBy && $task->createdBy->id !== $task->assigned_to) {
                    $dispatcher->notify(
                        notifiable: $task->createdBy,
                        category: 'tasks',
                        notificationType: 'task_overdue_escalated',
                        templateKey: $rule->template_key,
                        placeholders: [
                            'user_name'  => $task->createdBy->name,
                            'task_name'  => $task->title . ' (escalated — assignee has not responded)',
                            'event_name' => $task->event?->name ?? 'General',
                            'due_date'   => $task->due_date->format('D, d M Y'),
                        ],
                        priority: 'critical',
                        actionUrl: route('tenant.tasks.edit', $task->id),
                        actionLabel: 'View Task',
                        subject: $task,
                        tenantId: $tenantId,
                        reminderRuleId: $rule->id,
                    );
                }
            }
        }
    }


    /**
     * Notifies the CLIENT, not staff — the one genuinely new notifiable
     * case in this command. Every other branch here notifies a staff
     * member (createdBy, assignedTo, tenant owner); RSVP deadlines are
     * inherently client-relevant, so this deliberately breaks that pattern
     * rather than notifying staff about the client's own deadline.
     */
    private function processRsvpRule($rule, int $tenantId, $settings, NotificationDispatchService $dispatcher): void
    {
        if ($settings->isWithinQuietHours() && $rule->priority !== 'critical') return;

        $tenantModel = \App\Models\Central\Tenant::find($tenantId);
        if (!$tenantModel || !$tenantModel->clientNotificationEnabled('rsvp')) return;

        $forms = \App\Models\Tenant\RsvpForm::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->whereNotNull('deadline')
            ->with('event')
            ->get();

        foreach ($forms as $form) {
            $client = \App\Models\Tenant\ClientEventAccess::withoutGlobalScope('tenant')
                ->where('event_id', $form->event_id)
                ->where('tenant_id', $tenantId)
                ->with('client')
                ->first()?->client;

            if (!$client) continue;

            foreach ($rule->offsets as $offsetMinutes) {
                $targetTime = $form->deadline->copy()->addMinutes($offsetMinutes);

                if (!now()->between($targetTime, $targetTime->copy()->addMinutes(15))) continue;

                $alreadySent = \App\Models\Tenant\ReminderLog::where('tenant_id', $tenantId)
                    ->where('reminder_rule_id', $rule->id)
                    ->where('subject_type', \App\Models\Tenant\RsvpForm::class)
                    ->where('subject_id', $form->id)
                    ->where('sent_at', '>=', $targetTime->copy()->subHours(1))
                    ->exists();

                if ($alreadySent) continue;

                $remaining = $offsetMinutes <= -1440 ? '24 hours' : ($offsetMinutes <= -120 ? '2 hours' : 'soon');

                $dispatcher->notify(
                    notifiable: $client,
                    category: 'rsvp',
                    notificationType: 'rsvp_deadline_approaching',
                    templateKey: $rule->template_key,
                    placeholders: [
                        'user_name'      => $client->name,
                        'event_name'     => $form->event?->name ?? 'your event',
                        'due_date'       => $form->deadline->format('D, d M Y'),
                        'remaining_time' => $remaining,
                    ],
                    priority: $rule->priority,
                    actionUrl: route('client.dashboard'),
                    actionLabel: 'View Event',
                    subject: $form,
                    tenantId: $tenantId,
                    reminderRuleId: $rule->id,
                );
            }
        }
    }

    private function processRunsheetRule($rule, int $tenantId, NotificationDispatchService $dispatcher): void
    {
        // Event Day Mode gate — only scan runsheets whose date is TODAY
        $runsheets = \App\Models\Tenant\Runsheet::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->whereDate('date', today())
            ->where('status', 'active')
            ->with(['items.assignedTo'])
            ->get();

        foreach ($runsheets as $runsheet) {
            foreach ($runsheet->items as $item) {
                if (!$item->assignedTo || !$item->start_time) continue;
                if (in_array($item->status->value, ['done', 'delayed'])) continue;

                foreach ($rule->offsets as $offsetMinutes) {
                    $itemTime = \Carbon\Carbon::parse($item->start_time->format('H:i:s'));
                    $targetTime = $runsheet->date->copy()
                        ->setTime($itemTime->hour, $itemTime->minute, $itemTime->second)
                        ->addMinutes($offsetMinutes);

                    if (!now()->between($targetTime, $targetTime->copy()->addMinutes(10))) continue;

                    $alreadySent = \App\Models\Tenant\ReminderLog::where('tenant_id', $tenantId)
                        ->where('reminder_rule_id', $rule->id)
                        ->where('subject_type', \App\Models\Tenant\RunsheetItem::class)
                        ->where('subject_id', $item->id)
                        ->exists();
                    if ($alreadySent) continue;

                    $dispatcher->notify(
                        notifiable: $item->assignedTo,
                        category: 'runsheets',
                        notificationType: 'runsheet_item_starting_soon',
                        templateKey: $rule->template_key,
                        placeholders: [
                            'user_name'      => $item->assignedTo->name,
                            'task_name'      => $item->title,
                            'event_name'     => $runsheet->event->name ?? 'the event',
                            'remaining_time' => 'in 30 minutes',
                        ],
                        priority: $rule->priority,
                        actionUrl: route('tenant.events.runsheet', $runsheet->event->slug),
                        actionLabel: 'View Runsheet',
                        subject: $item,
                        tenantId: $tenantId,
                        reminderRuleId: $rule->id,
                    );
                }
            }
        }
    }
}