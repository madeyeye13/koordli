<?php
namespace App\Events;

use App\Models\Tenant\VendorInvoice;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InvoiceFullyPaid
{
    use Dispatchable, SerializesModels;
    public function __construct(public VendorInvoice $invoice) {}
}