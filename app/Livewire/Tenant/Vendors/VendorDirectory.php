<?php

namespace App\Livewire\Tenant\Vendors;

use App\Models\Tenant\Vendor;
use App\Models\Tenant\VendorCategory;
use App\Services\PermissionService;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Renderless;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.tenant')]
class VendorDirectory extends Component
{
    use WithPagination, WithToast;

    #[Url] public string $search        = '';
    #[Url] public string $categoryFilter = '';
    #[Url] public string $statusFilter   = '';
    #[Url] public string $view           = 'grid'; // grid | list

    public bool $showDeleteModal = false;
    public ?int $deleteId        = null;

    public bool $showInvolvementSettings = false;
    public string $involvementLevel = 'none';
    public string $disclaimerText = '';

    public function updatedSearch(): void         { $this->resetPage(); }
    public function updatedCategoryFilter(): void { $this->resetPage(); }
    public function updatedStatusFilter(): void   { $this->resetPage(); }

    #[Renderless]
    public function setView(string $view): void
    {
        $this->view = $view;
    }

    #[Renderless]
    public function togglePreferred(int $id): void
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'vendors.edit')) {
            $this->toastError('You do not have permission to edit vendors.');
            return;
        }

        $vendor = Vendor::find($id);
        if ($vendor) {
            $vendor->update(['is_preferred' => !$vendor->is_preferred]);
        }
    }

    public function confirmDelete(int $id): void
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'vendors.delete')) {
            $this->toastError('You do not have permission to delete vendors.');
            return;
        }

        $this->deleteId        = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'vendors.delete')) {
            $this->toastError('You do not have permission to delete vendors.');
            $this->showDeleteModal = false;
            return;
        }

        Vendor::find($this->deleteId)?->delete();
        $this->showDeleteModal = false;
        $this->deleteId        = null;
        $this->toastSuccess('Vendor deleted.');
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->deleteId        = null;
    }

    public function openInvolvementSettings(): void
    {
        if (!app(\App\Services\PermissionService::class)->userCan(auth()->user(), 'vendors.client_involvement.manage')) {
            $this->toastError('You do not have permission to manage client vendor involvement settings.');
            return;
        }

        $tenant = auth()->user()->tenant;
        $this->involvementLevel = $tenant->client_vendor_involvement_level ?? 'none';
        $this->disclaimerText = $tenant->vendor_disclaimer_text ?? '';
        $this->showInvolvementSettings = true;
    }

    public function saveInvolvementSettings(): void
    {
        if (!app(\App\Services\PermissionService::class)->userCan(auth()->user(), 'vendors.client_involvement.manage')) {
            $this->toastError('You do not have permission to manage client vendor involvement settings.');
            return;
        }

        $this->validate([
            'involvementLevel' => 'required|in:none,view_only,approve_selections,full_participation',
            'disclaimerText'   => 'nullable|string|max:2000',
        ]);

        auth()->user()->tenant->update([
            'client_vendor_involvement_level' => $this->involvementLevel,
            'vendor_disclaimer_text'           => $this->disclaimerText ?: null,
        ]);

        $this->showInvolvementSettings = false;
        $this->toastSuccess('Client vendor involvement settings saved.');
    }

    public function render()
    {
        abort_unless(
            app(PermissionService::class)->userCan(auth()->user(), 'vendors.view'),
            403
        );


        $canManageInvolvement = app(\App\Services\PermissionService::class)->userCan(auth()->user(), 'vendors.client_involvement.manage');
        $vendors = Vendor::with(['category', 'eventAssignments'])
            ->when($this->search, fn($q) =>
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('contact_name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
            )
            ->when($this->categoryFilter, fn($q) =>
                $q->where('vendor_category_id', $this->categoryFilter)
            )
            ->when($this->statusFilter === 'preferred', fn($q) =>
                $q->where('is_preferred', true)
            )
            ->when($this->statusFilter === 'active', fn($q) =>
                $q->where('is_active', true)
            )
            ->when($this->statusFilter === 'inactive', fn($q) =>
                $q->where('is_active', false)
            )
            ->orderByDesc('is_preferred')
            ->orderBy('name')
            ->paginate($this->view === 'grid' ? 12 : 15);

        return view('livewire.tenant.vendors.vendor-directory', [
            'vendors'               => $vendors,
            'categories'            => VendorCategory::orderBy('sort_order')->get(),
            'totalCount'            => Vendor::count(),
            'canManageInvolvement'  => $canManageInvolvement,
        ]);
    }
}