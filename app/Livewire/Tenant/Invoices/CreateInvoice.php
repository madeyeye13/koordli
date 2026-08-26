<?php

namespace App\Livewire\Tenant\Invoices;

use App\Models\Tenant\Event;
use App\Models\Tenant\Vendor;
use App\Models\Tenant\VendorContract;
use App\Models\Tenant\VendorEventAssignment;
use App\Models\Tenant\VendorInvoice;
use App\Services\PermissionService;
use App\Traits\WithToast;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.tenant')]
class CreateInvoice extends Component
{
    use WithToast, WithFileUploads;

    public ?int $vendor_id             = null;
    public ?int $event_id              = null;
    public ?int $vendor_contract_id    = null;
    public ?int $vendor_event_assignment_id = null;

    public string $title            = '';
    public string $issue_date       = '';
    public string $due_date         = '';
    public string $amount           = '';
    public string $tax_amount       = '0';
    public string $discount_amount  = '0';
    public string $notes            = '';
    public $attachment = null;

    public function mount(): void
    {
        abort_unless(
            app(PermissionService::class)->userCan(auth()->user(), 'invoices.manage'),
            403
        );

        $this->issue_date = now()->format('Y-m-d');
        $this->due_date   = now()->addDays(14)->format('Y-m-d');
    }

    public function updatedVendorId(): void
    {
        $this->event_id = null;
        $this->vendor_contract_id = null;
        $this->vendor_event_assignment_id = null;
    }

    public function updatedEventId(): void
    {
        if ($this->vendor_id && $this->event_id) {
            $assignment = VendorEventAssignment::where('vendor_id', $this->vendor_id)
                ->where('event_id', $this->event_id)
                ->first();
            $this->vendor_event_assignment_id = $assignment?->id;
        }
    }

    public function save(): void
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'invoices.manage')) {
            $this->toastError('You do not have permission to create invoices.');
            return;
        }

        $this->validate([
            'vendor_id'       => ['required', Rule::exists('vendors', 'id')->where('tenant_id', auth()->user()->tenant_id)],
            'issue_date'      => 'required|date',
            'due_date'        => 'nullable|date|after_or_equal:issue_date',
            'amount'          => 'required|numeric|min:0',
            'tax_amount'      => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'attachment'      => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $totalAmount = (float) $this->amount + (float) $this->tax_amount - (float) $this->discount_amount;

        $attachmentPath = null;
        $attachmentSize = null;
        if ($this->attachment) {
            $attachmentSize = $this->attachment->getSize();
            $attachmentPath = $this->attachment->store('invoices', 'public');
        }

        $invoice = VendorInvoice::create([
            'tenant_id'                  => auth()->user()->tenant_id,
            'vendor_id'                  => $this->vendor_id,
            'event_id'                   => $this->event_id ?: null,
            'vendor_contract_id'         => $this->vendor_contract_id ?: null,
            'vendor_event_assignment_id' => $this->vendor_event_assignment_id ?: null,
            'title'                      => $this->title ?: null,
            'issue_date'                 => $this->issue_date,
            'due_date'                   => $this->due_date ?: null,
            'amount'                     => $this->amount,
            'tax_amount'                 => $this->tax_amount ?: 0,
            'discount_amount'            => $this->discount_amount ?: 0,
            'total_amount'               => $totalAmount,
            'status'                     => 'draft',
            'notes'                      => $this->notes ?: null,
            'attachment_path'            => $attachmentPath,
            'attachment_size'            => $attachmentSize,
        ]);

        $this->toastSuccess('Invoice created.');
        $this->redirect(route('tenant.invoices.show', $invoice->uuid), navigate: true);
    }

    public function render()
    {
        $vendors    = Vendor::orderBy('name')->get(['id', 'name']);
        $events     = Event::orderByDesc('date')->get(['id', 'name', 'date']);
        $contracts  = $this->vendor_id
            ? VendorContract::where('vendor_id', $this->vendor_id)->orderByDesc('created_at')->get(['id', 'title'])
            : collect();

        return view('livewire.tenant.invoices.create-invoice', compact('vendors', 'events', 'contracts'));
    }
}