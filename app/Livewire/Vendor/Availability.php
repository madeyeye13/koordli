<?php

namespace App\Livewire\Vendor;

use App\Models\Tenant\VendorUnavailableDate;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.vendor')]
class Availability extends Component
{
    use WithToast;

    public bool   $showForm    = false;
    public string $date_from   = '';
    public string $date_to     = '';
    public string $reason      = 'personal';
    public string $notes       = '';

    public bool $showDeleteModal = false;
    public ?int $deleteId        = null;

    public function showAddForm(): void
    {
        $this->reset(['date_from', 'date_to', 'notes']);
        $this->reason    = 'personal';
        $this->showForm  = true;
    }

    public function save(): void
    {
        $this->validate([
            'date_from' => 'required|date|after_or_equal:today',
            'date_to'   => 'required|date|after_or_equal:date_from',
            'reason'    => 'required|in:personal,vacation,holiday,other',
            'notes'     => 'nullable|string|max:300',
        ], [
            'date_from.after_or_equal' => 'You can only block upcoming dates.',
        ]);

        $vendorAccount = auth('vendor')->user();

        VendorUnavailableDate::create([
            'tenant_id' => $vendorAccount->tenant_id,
            'vendor_id' => $vendorAccount->vendor_id,
            'date_from' => $this->date_from,
            'date_to'   => $this->date_to,
            'reason'    => $this->reason,
            'notes'     => $this->notes ?: null,
        ]);

        $this->showForm = false;
        $this->reset(['date_from', 'date_to', 'notes']);
        $this->toastSuccess('Unavailable dates added.');
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId        = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        $vendorAccount = auth('vendor')->user();

        VendorUnavailableDate::where('id', $this->deleteId)
            ->where('vendor_id', $vendorAccount->vendor_id)
            ->delete();

        $this->showDeleteModal = false;
        $this->deleteId        = null;
        $this->toastSuccess('Unavailable date removed.');
    }

    public function render()
    {
        $vendorAccount = auth('vendor')->user();

        $dates = VendorUnavailableDate::withoutGlobalScope('tenant')
            ->where('vendor_id', $vendorAccount->vendor_id)
            ->orderBy('date_from')
            ->get();

        $upcoming = $dates->filter(fn($d) => $d->date_to->isFuture() || $d->date_to->isToday());
        $past     = $dates->filter(fn($d) => $d->date_to->isPast() && !$d->date_to->isToday());

        return view('livewire.vendor.availability', compact('upcoming', 'past'));
    }
}