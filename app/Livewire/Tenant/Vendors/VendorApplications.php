<?php

namespace App\Livewire\Tenant\Vendors;

use App\Jobs\SendVendorApprovalJob;
use App\Models\Central\VendorAccount;
use App\Models\Tenant\Vendor;
use App\Models\Tenant\VendorApplication;
use App\Traits\WithToast;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Renderless;
use Livewire\Component;

#[Layout('layouts.tenant')]
class VendorApplications extends Component
{
    use WithToast;

    public string $filter           = 'pending';
    public string $rejectionReason  = '';
    public ?int   $rejectingId      = null;
    public bool   $showRejectModal  = false;

    #[Renderless]
    public function setFilter(string $filter): void
    {
        $this->filter = $filter;
    }

    public function approve(int $id): void
    {
        $application = VendorApplication::findOrFail($id);

        if (!$application->isPending()) {
            $this->toastError('This application has already been reviewed.');
            return;
        }

        // Check if vendor account already exists
        $existing = VendorAccount::where('tenant_id', $application->tenant_id)
            ->where('email', $application->email)
            ->first();

        if ($existing) {
            $application->update([
                'status'      => 'approved',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);
            $this->toastWarning('Application approved but vendor account already exists for this email.');
            return;
        }

        $password = Str::random(10);

        // Create vendor account
        $account = VendorAccount::create([
            'tenant_id'              => $application->tenant_id,
            'vendor_application_id'  => $application->id,
            'name'                   => $application->contact_name,
            'email'                  => $application->email,
            'password'               => Hash::make($password),
            'phone'                  => $application->phone,
            'business_name'          => $application->business_name,
            'is_active'              => true,
        ]);

        // Also add to vendor directory
        $vendor = Vendor::create([
            'tenant_id'          => $application->tenant_id,
            'vendor_category_id' => $application->vendor_category_id,
            'name'               => $application->business_name,
            'contact_name'       => $application->contact_name,
            'phone'              => $application->phone,
            'email'              => $application->email,
            'description'        => $application->service_description,
            'instagram'          => $application->instagram,
            'website'            => $application->website,
            'is_active'          => true,
        ]);

        // Link vendor account to vendor directory entry
        $account->update(['vendor_id' => $vendor->id]);

        $application->update([
            'status'      => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        SendVendorApprovalJob::dispatch(
            $application->email,
            $application->contact_name,
            $application->business_name,
            $password,
            auth()->user()->tenant->name,
        );

        $this->toastSuccess("{$application->business_name} approved and added to your vendor directory.");
    }

    public function openRejectModal(int $id): void
    {
        $this->rejectingId     = $id;
        $this->rejectionReason = '';
        $this->showRejectModal = true;
    }

    public function confirmReject(): void
    {
        $this->validate(['rejectionReason' => 'nullable|string|max:500']);

        $application = VendorApplication::findOrFail($this->rejectingId);
        $application->update([
            'status'           => 'rejected',
            'rejection_reason' => $this->rejectionReason,
            'reviewed_by'      => auth()->id(),
            'reviewed_at'      => now(),
        ]);

        $this->showRejectModal = false;
        $this->rejectingId     = null;
        $this->toastSuccess('Application rejected.');
    }

    public function cancelReject(): void
    {
        $this->showRejectModal = false;
        $this->rejectingId     = null;
    }

    public function render()
    {
        $applications = VendorApplication::where('status', $this->filter)
            ->with('category')
            ->orderByDesc('created_at')
            ->get();

        $counts = [
            'pending'  => VendorApplication::where('status', 'pending')->count(),
            'approved' => VendorApplication::where('status', 'approved')->count(),
            'rejected' => VendorApplication::where('status', 'rejected')->count(),
        ];

        return view('livewire.tenant.vendors.vendor-applications', compact('applications', 'counts'));
    }
}