<?php

namespace App\Livewire\Vendor;

use App\Enums\RunsheetItemStatus;
use App\Models\Tenant\RunsheetItem;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Renderless;
use Livewire\Component;

#[Layout('layouts.vendor')]
class VendorRunsheet extends Component
{
    use WithToast;

    // Delay note modal
    public bool   $showDelayModal = false;
    public ?int   $delayItemId    = null;
    public string $delayNote      = '';

    public function updateStatus(int $id, string $status): void
    {
        $vendor = auth('vendor')->user();

        $item = RunsheetItem::withoutGlobalScope('tenant')
            ->where('id', $id)
            ->where('vendor_id', $vendor->vendor_id)
            ->first();

        if (!$item) {
            $this->toastError('Item not found.');
            return;
        }

        if ($status === 'delayed') {
            $this->delayItemId    = $id;
            $this->showDelayModal = true;
            return;
        }

        $item->update(['status' => $status]);
        $this->toastSuccess('Status updated.');
    }

    public function confirmDelay(): void
    {
        $vendor = auth('vendor')->user();

        $item = RunsheetItem::withoutGlobalScope('tenant')
            ->where('id', $this->delayItemId)
            ->where('vendor_id', $vendor->vendor_id)
            ->first();

        if ($item) {
            $item->update([
                'status' => 'delayed',
                'notes'  => $this->delayNote ?: $item->notes,
            ]);
            $this->toastSuccess('Marked as delayed.');
        }

        $this->showDelayModal = false;
        $this->delayItemId    = null;
        $this->delayNote      = '';
    }

    public function cancelDelay(): void
    {
        $this->showDelayModal = false;
        $this->delayItemId    = null;
        $this->delayNote      = '';
    }

    public function render()
    {
        $vendor = auth('vendor')->user();

        $items = RunsheetItem::withoutGlobalScope('tenant')
            ->where('tenant_id', $vendor->tenant_id)
            ->where('vendor_id', $vendor->vendor_id)
            ->with(['runsheet.event'])
            ->orderBy('start_time')
            ->get()
            ->groupBy(fn($item) => $item->runsheet?->event?->name ?? 'Unknown Event');

        return view('livewire.vendor.vendor-runsheet', compact('items'));
    }
}