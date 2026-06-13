<?php

namespace App\Livewire\Tenant\Vendors;

use App\Models\Tenant\Vendor;
use App\Models\Tenant\VendorCategory;
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
        $vendor = Vendor::find($id);
        if ($vendor) {
            $vendor->update(['is_preferred' => !$vendor->is_preferred]);
        }
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId        = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
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

    public function render()
    {
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
            'vendors'    => $vendors,
            'categories' => VendorCategory::orderBy('sort_order')->get(),
            'totalCount' => Vendor::count(),
        ]);
    }
}