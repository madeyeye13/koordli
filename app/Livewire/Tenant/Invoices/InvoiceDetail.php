<?php

namespace App\Livewire\Tenant\Invoices;

use App\Models\Tenant\VendorInvoice;
use App\Models\Tenant\VendorInvoicePayment;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.tenant')]
class InvoiceDetail extends Component
{
    use WithToast, WithFileUploads;

    public VendorInvoice $invoice;

    public bool   $showPaymentForm = false;
    public string $pay_amount      = '';
    public string $pay_date        = '';
    public string $pay_method      = 'bank_transfer';
    public string $pay_reference   = '';
    public string $pay_notes       = '';
    public $pay_receipt = null;

    public bool $showDeletePaymentModal = false;
    public ?int $deletePaymentId         = null;

    public bool $showCancelModal = false;

    public function mount(string $uuid): void
    {
        $this->invoice = VendorInvoice::where('uuid', $uuid)
            ->with(['vendor', 'event', 'contract', 'payments.recordedBy', 'createdBy'])
            ->firstOrFail();
    }

    public function markAsSent(): void
    {
        $this->invoice->update(['status' => 'sent']);
        $this->invoice->refresh();
        $this->toastSuccess('Invoice marked as sent.');
    }

    public function showAddPayment(): void
    {
        $this->reset(['pay_amount', 'pay_reference', 'pay_notes']);
        $this->pay_date   = now()->format('Y-m-d');
        $this->pay_method = 'bank_transfer';
        $this->pay_amount = (string) $this->invoice->balance();
        $this->showPaymentForm = true;
    }

    public function recordPayment(): void
    {
        $this->validate([
            'pay_amount'   => 'required|numeric|min:0.01',
            'pay_date'     => 'required|date',
            'pay_method'   => 'required|in:cash,bank_transfer,card,other',
            'pay_receipt'  => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $receiptPath = null;
        if ($this->pay_receipt) {
            $receiptPath = $this->pay_receipt->store('invoice-payments', 'public');
        }

        VendorInvoicePayment::create([
            'tenant_id'         => auth()->user()->tenant_id,
            'vendor_invoice_id' => $this->invoice->id,
            'amount'            => $this->pay_amount,
            'paid_on'           => $this->pay_date,
            'payment_method'    => $this->pay_method,
            'reference'         => $this->pay_reference ?: null,
            'notes'             => $this->pay_notes ?: null,
            'receipt_path'      => $receiptPath,
        ]);

        $this->invoice->refresh();
        $this->showPaymentForm = false;
        $this->toastSuccess('Payment recorded.');
    }

    public function confirmDeletePayment(int $id): void
    {
        $this->deletePaymentId         = $id;
        $this->showDeletePaymentModal  = true;
    }

    public function deletePayment(): void
    {
        VendorInvoicePayment::find($this->deletePaymentId)?->delete();
        $this->invoice->refresh();
        $this->showDeletePaymentModal = false;
        $this->deletePaymentId        = null;
        $this->toastSuccess('Payment removed.');
    }

    public function confirmCancel(): void
    {
        $this->showCancelModal = true;
    }

    public function cancelInvoice(): void
    {
        $this->invoice->update(['status' => 'cancelled']);
        $this->invoice->budgetItem?->delete();
        $this->invoice->refresh();
        $this->showCancelModal = false;
        $this->toastSuccess('Invoice cancelled.');
    }

    public function render()
    {
        return view('livewire.tenant.invoices.invoice-detail');
    }
}