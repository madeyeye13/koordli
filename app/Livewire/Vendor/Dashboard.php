<?php

namespace App\Livewire\Vendor;

use App\Models\Tenant\VendorEventAssignment;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.vendor')]
class Dashboard extends Component
{
    public function render()
    {
        $vendor = auth('vendor')->user();

        $assignments = VendorEventAssignment::withoutGlobalScope('tenant')
            ->where('tenant_id', $vendor->tenant_id)
            ->where('vendor_id', $vendor->vendor_id)
            ->with(['event.eventType', 'event.status'])
            ->orderByDesc('created_at')
            ->get();

        return view('livewire.vendor.dashboard', compact('assignments'));
    }
}