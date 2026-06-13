<?php

namespace App\Livewire\Tenant\Vendors;

use App\Models\Tenant\Vendor;
use App\Models\Tenant\VendorEventAssignment;
use App\Models\Tenant\Event;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.tenant')]
class VendorDetail extends Component
{
    use WithToast;

    public Vendor $vendor;

    // Assign to event form
    public bool   $showAssignForm  = false;
    public ?int   $assign_event_id = null;
    public string $assign_amount   = '';
    public string $assign_notes    = '';
    public string $assign_status   = 'pending';

    // Edit assignment
    public ?int   $editAssignId     = null;
    public string $editAmountAgreed = '';
    public string $editAmountPaid   = '';
    public string $editStatus       = '';
    public string $editNotes        = '';

    // Delete assignment
    public bool $showDeleteAssign = false;
    public ?int $deleteAssignId   = null;

    public function mount(int $id): void
    {
        $this->vendor = Vendor::with(['category', 'eventAssignments.event'])->findOrFail($id);
    }

    public function assignToEvent(): void
    {
        $this->validate([
            'assign_event_id' => 'required|exists:events,id',
            'assign_amount'   => 'nullable|numeric|min:0',
            'assign_status'   => 'required|in:pending,confirmed,cancelled',
            'assign_notes'    => 'nullable|string|max:500',
        ], [], ['assign_event_id' => 'event']);

        // Check not already assigned
        $exists = VendorEventAssignment::where('vendor_id', $this->vendor->id)
            ->where('event_id', $this->assign_event_id)
            ->exists();

        if ($exists) {
            $this->addError('assign_event_id', 'This vendor is already assigned to that event.');
            return;
        }

        VendorEventAssignment::create([
            'tenant_id'     => auth()->user()->tenant_id,
            'vendor_id'     => $this->vendor->id,
            'event_id'      => $this->assign_event_id,
            'amount_agreed' => $this->assign_amount ?: 0,
            'amount_paid'   => 0,
            'status'        => $this->assign_status,
            'notes'         => $this->assign_notes ?: null,
        ]);

        $this->vendor->load('eventAssignments.event');
        $this->reset(['showAssignForm', 'assign_event_id', 'assign_amount', 'assign_notes']);
        $this->assign_status = 'pending';
        $this->toastSuccess('Vendor assigned to event.');
    }

    public function startEditAssignment(int $assignId): void
    {
        $assign = VendorEventAssignment::find($assignId);
        if (!$assign) return;
        $this->editAssignId     = $assignId;
        $this->editAmountAgreed = $assign->amount_agreed;
        $this->editAmountPaid   = $assign->amount_paid;
        $this->editStatus       = $assign->status;
        $this->editNotes        = $assign->notes ?? '';
    }

    public function saveEditAssignment(): void
    {
        $this->validate([
            'editAmountAgreed' => 'nullable|numeric|min:0',
            'editAmountPaid'   => 'nullable|numeric|min:0',
            'editStatus'       => 'required|in:pending,confirmed,cancelled',
        ]);

        $assign = VendorEventAssignment::find($this->editAssignId);
        if ($assign) {
            $assign->update([
                'amount_agreed' => $this->editAmountAgreed ?: 0,
                'amount_paid'   => $this->editAmountPaid ?: 0,
                'status'        => $this->editStatus,
                'notes'         => $this->editNotes ?: null,
            ]);
        }

        $this->vendor->load('eventAssignments.event');
        $this->reset(['editAssignId', 'editAmountAgreed', 'editAmountPaid', 'editStatus', 'editNotes']);
        $this->toastSuccess('Assignment updated.');
    }

    public function cancelEditAssignment(): void
    {
        $this->reset(['editAssignId', 'editAmountAgreed', 'editAmountPaid', 'editStatus', 'editNotes']);
    }

    public function confirmDeleteAssignment(int $assignId): void
    {
        $this->deleteAssignId   = $assignId;
        $this->showDeleteAssign = true;
    }

    public function deleteAssignment(): void
    {
        VendorEventAssignment::find($this->deleteAssignId)?->delete();
        $this->vendor->load('eventAssignments.event');
        $this->showDeleteAssign = false;
        $this->deleteAssignId   = null;
        $this->toastSuccess('Assignment removed.');
    }

    public function render()
    {
        $availableEvents = Event::whereDoesntHave('vendorAssignments', fn($q) =>
            $q->where('vendor_id', $this->vendor->id)
        )->orderBy('date')->get(['id', 'name', 'slug', 'date']);

        return view('livewire.tenant.vendors.vendor-detail', [
            'availableEvents' => $availableEvents,
        ]);
    }
}