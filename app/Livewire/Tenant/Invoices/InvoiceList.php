<?php

namespace App\Livewire\Tenant\Invoices;

use App\Models\Tenant\VendorInvoice;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.tenant')]
class InvoiceList extends Component
{
    #[Url] public string $statusFilter = '';
    #[Url] public string $search       = '';

    public function render()
    {
        $invoices = VendorInvoice::with(['vendor', 'event'])
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->search, fn($q) => $q->where('invoice_number', 'like', '%' . $this->search . '%')
                ->orWhereHas('vendor', fn($v) => $v->where('name', 'like', '%' . $this->search . '%')))
            ->orderByDesc('issue_date')
            ->get();

        // Auto-flag overdue on view
        foreach ($invoices as $invoice) {
            if ($invoice->isOverdue() && $invoice->status !== 'overdue') {
                $invoice->update(['status' => 'overdue']);
            }
        }

        $stats = [
            'total_invoiced'    => VendorInvoice::whereNotIn('status', ['cancelled'])->sum('total_amount'),
            'total_paid'        => VendorInvoice::whereNotIn('status', ['cancelled'])->get()->sum(fn($i) => $i->totalPaid()),
            'total_outstanding' => VendorInvoice::whereNotIn('status', ['cancelled'])->get()->sum(fn($i) => $i->balance()),
            'overdue_count'     => VendorInvoice::where('status', 'overdue')->count(),
        ];

        return view('livewire.tenant.invoices.invoice-list', compact('invoices', 'stats'));
    }
}