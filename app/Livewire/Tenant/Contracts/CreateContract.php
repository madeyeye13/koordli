<?php

namespace App\Livewire\Tenant\Contracts;

use App\Models\Tenant\Event;
use App\Models\Tenant\Vendor;
use App\Models\Tenant\VendorContract;
use App\Models\Tenant\VendorContractTemplate;
use App\Models\Tenant\VendorEventAssignment;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.tenant')]
class CreateContract extends Component
{
    use WithToast;

    public ?int $preselectVendorId = null;

    public ?int $vendor_id      = null;
    public ?int $event_id       = null;
    public ?int $assignment_id  = null;
    public ?int $template_id    = null;

    public string $title             = '';
    public string $content           = '';
    public string $contract_amount   = '';
    public string $payment_schedule  = '';
    public string $expires_at        = '';

    public function mount(?int $vendorId = null): void
    {
        $this->preselectVendorId = $vendorId;
        $this->vendor_id         = $vendorId;
    }

    public function selectTemplate(int $templateId): void
    {
        $template = VendorContractTemplate::find($templateId);
        if (!$template || !$this->vendor_id) return;

        $vendor = Vendor::find($this->vendor_id);
        $event  = $this->event_id ? Event::find($this->event_id) : null;

        $this->template_id = $templateId;
        $this->title        = $template->name . ' — ' . $vendor->name;
        $this->content      = $template->render(
            $vendor,
            $event,
            $this->contract_amount ?: null,
            $this->payment_schedule ?: null,
            auth()->user()->name,
        );
    }

    public function updatedVendorId(): void
    {
        $this->assignment_id = null;
        $this->event_id      = null;
    }

    public function updatedEventId(): void
    {
        if ($this->vendor_id && $this->event_id) {
            $assignment = VendorEventAssignment::where('vendor_id', $this->vendor_id)
                ->where('event_id', $this->event_id)
                ->first();
            $this->assignment_id = $assignment?->id;
        }
    }

    public function save(): void
    {
        $this->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'title'     => 'required|string|min:2|max:200',
            'content'   => 'required|string|min:10',
        ]);

        $contract = VendorContract::create([
            'tenant_id'                  => auth()->user()->tenant_id,
            'vendor_id'                  => $this->vendor_id,
            'event_id'                   => $this->event_id ?: null,
            'vendor_event_assignment_id' => $this->assignment_id ?: null,
            'template_id'                => $this->template_id ?: null,
            'title'                      => $this->title,
            'content'                    => $this->content,
            'contract_amount'            => $this->contract_amount ?: null,
            'payment_schedule'           => $this->payment_schedule ?: null,
            'status'                     => 'draft',
            'expires_at'                 => $this->expires_at ?: null,
        ]);

        $contract->changeStatus('draft', 'Contract created.');

        $this->toastSuccess('Contract created.');
        $this->redirect(route('tenant.contracts.show', $contract->uuid), navigate: true);
    }

    public function render()
    {
        $vendors    = Vendor::orderBy('name')->get(['id', 'name']);
       $events = Event::orderByDesc('date')->get(['id', 'name', 'date']);
        $templates  = VendorContractTemplate::where('is_active', true)->orderBy('name')->get();

        return view('livewire.tenant.contracts.create-contract', compact('vendors', 'events', 'templates'));
    }
}