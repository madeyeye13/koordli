<?php

namespace App\Console\Commands;

use App\Models\Tenant\VendorEventAssignment;
use App\Models\Tenant\VendorInvoice;
use App\Models\Tenant\VendorInvoicePayment;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class BackfillVendorInvoicesFromAssignments extends Command
{
    protected $signature   = 'koordli:backfill-vendor-invoices';
    protected $description = 'Convert existing VendorEventAssignment amount_agreed/amount_paid into proper VendorInvoice + payment records';

    public function handle(): void
    {
        $assignments = VendorEventAssignment::withoutGlobalScope('tenant')
            ->where('amount_agreed', '>', 0)
            ->whereDoesntHave('invoices') // skip if already backfilled
            ->with(['vendor', 'event'])
            ->get();

        if ($assignments->isEmpty()) {
            $this->info('No assignments need backfilling.');
            return;
        }

        $this->info("Found {$assignments->count()} assignment(s) to backfill.");

        foreach ($assignments as $assignment) {
            $invoice = VendorInvoice::create([
                'uuid'                       => Str::uuid(),
                'tenant_id'                  => $assignment->tenant_id,
                'vendor_id'                  => $assignment->vendor_id,
                'event_id'                   => $assignment->event_id,
                'vendor_event_assignment_id' => $assignment->id,
                'title'                      => 'Service Agreement (migrated)',
                'issue_date'                 => $assignment->created_at->format('Y-m-d'),
                'due_date'                   => null,
                'amount'                     => $assignment->amount_agreed,
                'tax_amount'                 => 0,
                'discount_amount'            => 0,
                'total_amount'               => $assignment->amount_agreed,
                'status'                     => 'sent',
                'notes'                      => 'Auto-migrated from vendor event assignment on ' . now()->format('d M Y'),
            ]);

            if ((float) $assignment->amount_paid > 0) {
                VendorInvoicePayment::create([
                    'tenant_id'         => $assignment->tenant_id,
                    'vendor_invoice_id' => $invoice->id,
                    'amount'            => $assignment->amount_paid,
                    'paid_on'           => $assignment->updated_at->format('Y-m-d'),
                    'payment_method'    => 'other',
                    'notes'             => 'Migrated payment from previous assignment record.',
                ]);
            }

            $invoice->recalculateStatus();

            $this->info("✓ Created invoice {$invoice->invoice_number} for {$assignment->vendor->name} — {$assignment->event?->name}");
        }

        $this->info('Backfill complete.');
    }
}