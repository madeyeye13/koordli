<?php

namespace App\Listeners;

use App\Events\InvoiceFullyPaid;
use App\Models\Tenant\ActivityTimeline;
use App\Services\Notifications\NotificationDispatchService;
use Illuminate\Contracts\Queue\ShouldQueue;

class LogInvoiceActivity implements ShouldQueue
{
    public function handle(InvoiceFullyPaid $event): void
    {
        $invoice = $event->invoice;

        ActivityTimeline::log($invoice, 'invoice_fully_paid', 'Invoice ' . $invoice->invoice_number . ' fully paid');

        if ($invoice->createdBy) {
            app(NotificationDispatchService::class)->notify(
                notifiable: $invoice->createdBy,
                category: 'invoices',
                notificationType: 'invoice_fully_paid',
                templateKey: 'invoice_fully_paid',
                placeholders: [
                    'user_name'  => $invoice->createdBy->name,
                    'task_name'  => $invoice->vendor->name,
                    'event_name' => $invoice->invoice_number,
                ],
                priority: 'normal',
                actionUrl: route('tenant.invoices.show', $invoice->uuid),
                actionLabel: 'View Invoice',
                subject: $invoice,
                tenantId: $invoice->tenant_id,
            );
        }

        // Note: vendor-facing notifications (notifying VendorAccount) are deferred —
        // VendorAccount doesn't yet use the Notifiable trait / notification_preferences
        // system, matching the "client/vendor preferences are MVP-deferred" decision
        // from the original architecture discussion.
    }
}