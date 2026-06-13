<?php

namespace App\Livewire\Tenant\Vendors;

use App\Jobs\SendVendorAssignedJob;
use App\Jobs\SendVendorInviteJob;
use App\Models\Central\VendorAccount;
use App\Models\Tenant\Event;
use App\Models\Tenant\Vendor;
use App\Models\Tenant\VendorEventAssignment;
use App\Traits\WithToast;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.tenant')]
class VendorDetail extends Component
{
    use WithToast;

    public Vendor $vendor;

    public bool   $showAssignForm     = false;
    public ?int   $assign_event_id    = null;
    public string $assign_amount      = '';
    public string $assign_notes       = '';
    public string $assign_amount_paid = '';
    public string $assign_status      = 'pending';

    public ?int   $editAssignId     = null;
    public string $editAmountAgreed = '';
    public string $editAmountPaid   = '';
    public string $editStatus       = '';
    public string $editNotes        = '';

    public bool $showDeleteAssign = false;
    public ?int $deleteAssignId   = null;

    public function mount(int $id): void
    {
        $this->vendor = Vendor::with([
            'category',
            'eventAssignments.event',
        ])->findOrFail($id);
    }

    public function assignToEvent(): void
    {
        $this->validate([
            'assign_event_id' => 'required|exists:events,id',
            'assign_amount'   => 'nullable|numeric|min:0',
            'assign_status'   => 'required|in:pending,confirmed,cancelled',
            'assign_notes'    => 'nullable|string|max:500',
        ], [], ['assign_event_id' => 'event']);

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
            'amount_paid'   => $this->assign_amount_paid ?: 0,
            'status'        => $this->assign_status,
            'notes'         => $this->assign_notes ?: null,
        ]);

        // Send notification email if vendor has email
        if ($this->vendor->email) {
            $tenant    = auth()->user()->tenant;
            $event     = Event::find($this->assign_event_id);
            $eventName = $event?->name ?? 'Upcoming Event';
            $eventDate = $event?->date?->format('D, d M Y') ?? 'TBC';

            $existing = VendorAccount::where('tenant_id', $tenant->id)
                ->where('email', $this->vendor->email)
                ->first();

            if ($existing) {
                // Already has account — send assignment notification only
                SendVendorAssignedJob::dispatch(
                    $this->vendor->email,
                    $existing->name,
                    $this->vendor->name,
                    $eventName,
                    $eventDate,
                    $tenant->name,
                    false,
                );
            } else {
                // No account — create one and send combined email
                $password = Str::random(10);

                $account = VendorAccount::create([
                    'tenant_id'      => $tenant->id,
                    'vendor_id'      => $this->vendor->id,
                    'name'           => $this->vendor->contact_name ?? $this->vendor->name,
                    'email'          => $this->vendor->email,
                    'password'       => Hash::make($password),
                    'phone'          => $this->vendor->phone,
                    'business_name'  => $this->vendor->name,
                    'is_active'      => true,
                    'password_changed' => false,
                ]);

                SendVendorAssignedJob::dispatch(
                    $this->vendor->email,
                    $account->name,
                    $this->vendor->name,
                    $eventName,
                    $eventDate,
                    $tenant->name,
                    true,
                    $password,
                );
            }
        }

        $this->vendor->load('eventAssignments.event');
        $this->reset(['showAssignForm', 'assign_event_id', 'assign_amount', 'assign_amount_paid', 'assign_notes']);
        $this->assign_status = 'pending';
        $this->toastSuccess('Vendor assigned to event successfully.');
    }

    public function inviteVendor(): void
    {
        if (empty($this->vendor->email)) {
            $this->toastError('This vendor has no email address. Edit the vendor first.');
            return;
        }

        $tenant = auth()->user()->tenant;

        $existing = VendorAccount::where('tenant_id', $tenant->id)
            ->where('email', $this->vendor->email)
            ->first();

        if ($existing) {
            $this->toastWarning('This vendor has already been invited. A portal account exists for ' . $this->vendor->email);
            return;
        }

        $password = Str::random(10);

        VendorAccount::create([
            'tenant_id'        => $tenant->id,
            'vendor_id'        => $this->vendor->id,
            'name'             => $this->vendor->contact_name ?? $this->vendor->name,
            'email'            => $this->vendor->email,
            'password'         => Hash::make($password),
            'phone'            => $this->vendor->phone,
            'business_name'    => $this->vendor->name,
            'is_active'        => true,
            'password_changed' => false,
        ]);

        SendVendorInviteJob::dispatch(
            $this->vendor->email,
            $this->vendor->contact_name ?? $this->vendor->name,
            $this->vendor->name,
            $password,
            $tenant->name,
        );

        $this->toastSuccess('Portal invite sent to ' . $this->vendor->email);
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