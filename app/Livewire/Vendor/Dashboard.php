<?php

namespace App\Livewire\Vendor;

use App\Models\Tenant\RunsheetItem;
use App\Models\Tenant\Task;
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

        // Tasks assigned to this vendor account
        $tasks = Task::withoutGlobalScope('tenant')
            ->where('tenant_id', $vendor->tenant_id)
            ->where('vendor_account_id', $vendor->id)
            ->with(['event'])
            ->orderBy('due_date')
            ->get();

        // Runsheet items assigned to this vendor (via vendor_id → vendors.id)
        $runsheetItems = RunsheetItem::withoutGlobalScope('tenant')
            ->where('tenant_id', $vendor->tenant_id)
            ->where('vendor_id', $vendor->vendor_id)
            ->with(['runsheet.event'])
            ->orderBy('start_time')
            ->get();

        return view('livewire.vendor.dashboard', compact('assignments', 'tasks', 'runsheetItems'));
    }
}