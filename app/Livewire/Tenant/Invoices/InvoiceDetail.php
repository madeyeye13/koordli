<?php

namespace App\Livewire\Tenant\Invoices;

use App\Models\Tenant\VendorInvoice;
use App\Models\Tenant\VendorInvoicePayment;
use App\Services\PermissionService;
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
        abort_unless(
            app(PermissionService::class)->userCan(auth()->user(), 'invoices.view')
                || app(PermissionService::class)->userCan(auth()->user(), 'invoices.manage'),
            403
        );

        $this->invoice = VendorInvoice::where('uuid', $uuid)
            ->with(['vendor', 'event', 'contract', 'payments.recordedBy', 'createdBy'])
            ->firstOrFail();
    }

    private function requireManage(): bool
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'invoices.manage')) {
            $this->toastError('You do not have permission to manage invoices.');
            return false;
        }
        return true;
    }

    public function markAsSent(): void
    {
        if (!$this->requireManage()) return;

        $this->invoice->update(['status' => 'sent']);
        $this->invoice->refresh();
        $this->toastSuccess('Invoice marked as sent.');
    }

    public function showAddPayment(): void
    {
        if (!$this->requireManage()) return;

        $this->reset(['pay_amount', 'pay_reference', 'pay_notes']);
        $this->pay_date   = now()->format('Y-m-d');
        $this->pay_method = 'bank_transfer';
        $this->pay_amount = (string) $this->invoice->balance();
        $this->showPaymentForm = true;
    }

    public function recordPayment(): void
    {
        if (!$this->requireManage()) return;

        $this->validate([
            'pay_amount'   => 'required|numeric|min:0.01',
            'pay_date'     => 'required|date',
            'pay_method'   => 'required|in:cash,bank_transfer,card,other',
            'pay_receipt'  => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $receiptPath = null;
        $receiptSize = null;
        if ($this->pay_receipt) {
            $receiptSize = $this->pay_receipt->getSize();
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
            'receipt_size'      => $receiptSize,
        ]);

        $this->invoice->refresh();
        $this->showPaymentForm = false;
        $this->toastSuccess('Payment recorded.');
    }

    public function confirmDeletePayment(int $id): void
    {
        if (!$this->requireManage()) return;

        $this->deletePaymentId         = $id;
        $this->showDeletePaymentModal  = true;
    }

    public function deletePayment(): void
    {
        if (!$this->requireManage()) return;

        VendorInvoicePayment::find($this->deletePaymentId)?->delete();
        $this->invoice->refresh();
        $this->showDeletePaymentModal = false;
        $this->deletePaymentId        = null;
        $this->toastSuccess('Payment removed.');
    }

    public function confirmCancel(): void
    {
        if (!$this->requireManage()) return;

        $this->showCancelModal = true;
    }

    public function cancelInvoice(): void
    {
        if (!$this->requireManage()) return;

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