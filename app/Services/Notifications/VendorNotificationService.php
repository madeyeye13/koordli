<?php

namespace App\Services\Notifications;

use App\Models\Central\VendorAccount;
use App\Models\Tenant\RunsheetItem;
use App\Models\Tenant\Task;
use App\Models\Tenant\User;
use App\Models\Tenant\VendorContract;
use App\Models\Tenant\VendorEventAssignment;

class VendorNotificationService
{
    public function __construct(private NotificationDispatchService $dispatcher) {}

    public function notifyTaskAssigned(Task $task): void
    {
        $vendorAccount = $this->vendorAccountFor($task->vendor_account_id, $task->tenant_id);
        if (!$vendorAccount) return;

        $this->dispatcher->notify(
            notifiable: $vendorAccount,
            category: 'tasks',
            notificationType: 'vendor_task_assigned',
            templateKey: 'vendor_task_assigned',
            placeholders: [
                'user_name'  => $vendorAccount->name,
                'task_name'  => $task->title,
                'event_name' => $task->event?->name ?? 'General',
                'due_date'   => $task->due_date?->format('D, d M Y') ?? 'No due date',
            ],
            priority: 'normal',
            actionUrl: route('vendor.dashboard'),
            actionLabel: 'View Task',
            subject: $task,
            tenantId: $task->tenant_id,
        );
    }

    /**
     * The reverse direction from RunsheetManager's existing
     * RunsheetItemDelayed event (staff → vendor) — this notifies STAFF
     * when a vendor marks their own runsheet item as delayed, which
     * previously dispatched nothing at all.
     */
    public function notifyRunsheetDelayedByVendor(RunsheetItem $item): void
    {
        $owners = User::withoutGlobalScope('tenant')
            ->where('tenant_id', $item->tenant_id)
            ->whereHas('roles', fn($q) => $q->where('is_system', true))
            ->get();

        $event = $item->runsheet?->event;

        foreach ($owners as $owner) {
            $this->dispatcher->notify(
                notifiable: $owner,
                category: 'runsheets',
                notificationType: 'vendor_marked_delayed',
                templateKey: 'vendor_marked_delayed',
                placeholders: [
                    'user_name'  => $owner->name,
                    'task_name'  => $item->title,
                    'event_name' => $event?->name ?? 'the event',
                ],
                priority: 'high',
                actionUrl: $event ? route('tenant.events.runsheet', $event->slug) : route('tenant.dashboard'),
                actionLabel: 'View Runsheet',
                subject: $item,
                tenantId: $item->tenant_id,
            );
        }
    }

    public function notifyBookingCreated(VendorEventAssignment $assignment): void
    {
        $vendorAccount = $this->vendorAccountForVendor($assignment->vendor_id, $assignment->tenant_id);
        if (!$vendorAccount) return;

        $this->dispatcher->notify(
            notifiable: $vendorAccount,
            category: 'bookings',
            notificationType: 'vendor_booking_created',
            templateKey: 'vendor_booking_created',
            placeholders: [
                'user_name'  => $vendorAccount->name,
                'event_name' => $assignment->event?->name ?? 'an event',
            ],
            priority: 'normal',
            actionUrl: route('vendor.dashboard'),
            actionLabel: 'View Booking',
            subject: $assignment,
            tenantId: $assignment->tenant_id,
        );
    }

    public function notifyBookingStatusChanged(VendorEventAssignment $assignment): void
    {
        $vendorAccount = $this->vendorAccountForVendor($assignment->vendor_id, $assignment->tenant_id);
        if (!$vendorAccount) return;

        $this->dispatcher->notify(
            notifiable: $vendorAccount,
            category: 'bookings',
            notificationType: 'vendor_booking_status_changed',
            templateKey: 'vendor_booking_status_changed',
            placeholders: [
                'user_name'  => $vendorAccount->name,
                'event_name' => $assignment->event?->name ?? 'an event',
                'status'     => ucfirst($assignment->status),
            ],
            priority: 'normal',
            actionUrl: route('vendor.dashboard'),
            actionLabel: 'View Booking',
            subject: $assignment,
            tenantId: $assignment->tenant_id,
        );
    }

    public function notifyContractSent(VendorContract $contract): void
    {
        $vendorAccount = $this->vendorAccountForVendor($contract->vendor_id, $contract->tenant_id);
        if (!$vendorAccount) return;

        $this->dispatcher->notify(
            notifiable: $vendorAccount,
            category: 'contracts',
            notificationType: 'vendor_contract_sent',
            templateKey: 'vendor_contract_sent',
            placeholders: [
                'user_name'     => $vendorAccount->name,
                'contract_name' => $contract->title,
            ],
            priority: 'high',
            actionUrl: route('vendor.dashboard'),
            actionLabel: 'View Contract',
            subject: $contract,
            tenantId: $contract->tenant_id,
        );
    }

    private function vendorAccountFor(?int $vendorAccountId, int $tenantId): ?VendorAccount
    {
        if (!$vendorAccountId) return null;
        return VendorAccount::where('id', $vendorAccountId)->where('tenant_id', $tenantId)->first();
    }

    /**
     * Resolves the VendorAccount belonging to a given Vendor (directory
     * record), not the same as vendorAccountFor() above which looks up
     * by the account's own ID directly — Task.vendor_account_id points
     * at the account, but VendorEventAssignment/VendorContract point at
     * the Vendor directory record, requiring one extra lookup step.
     */
    private function vendorAccountForVendor(?int $vendorId, int $tenantId): ?VendorAccount
    {
        if (!$vendorId) return null;
        return VendorAccount::where('vendor_id', $vendorId)->where('tenant_id', $tenantId)->first();
    }
}