<?php

namespace App\Livewire\Tenant\Assets;

use App\Models\Tenant\Asset;
use App\Models\Tenant\AssetCategory;
use App\Services\PermissionService;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.tenant')]
class AssetList extends Component
{
    use WithToast;

    #[Url] public string $search = '';
    #[Url] public string $statusFilter = '';
    #[Url] public string $categoryFilter = '';

    public bool $showDeleteModal = false;
    public ?int $deleteId = null;

    public function confirmDelete(int $id): void
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'assets.manage')) {
            $this->toastError('You do not have permission to manage assets.');
            return;
        }

        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(?int $id = null): void
    {
        if (!is_null($id)) {
            $this->deleteId = $id;
        }

        if (!app(PermissionService::class)->userCan(auth()->user(), 'assets.manage')) {
            $this->toastError('You do not have permission to manage assets.');
            $this->showDeleteModal = false;
            return;
        }

        Asset::where('tenant_id', auth()->user()->tenant_id)->find($this->deleteId)?->delete();
        $this->showDeleteModal = false;
        $this->deleteId = null;
        $this->toastSuccess(term_title('asset', 'Asset') . ' deleted.');
    }

    public function render()
    {
        abort_unless(
            app(PermissionService::class)->userCan(auth()->user(), 'assets.manage'),
            403
        );

        $tenantId = auth()->user()->tenant_id;
        $assets = Asset::where('tenant_id', $tenantId)->with(['category'])
            ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->categoryFilter, fn($q) => $q->where('asset_category_id', $this->categoryFilter))
            ->orderBy('name')
            ->get();

        $categories = AssetCategory::where('tenant_id', $tenantId)->orderBy('sort_order')->get();

        return view('livewire.tenant.assets.asset-list', compact('assets', 'categories'));
    }
}