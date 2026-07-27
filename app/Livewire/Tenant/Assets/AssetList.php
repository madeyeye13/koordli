<?php

namespace App\Livewire\Tenant\Assets;

use App\Models\Tenant\Asset;
use App\Models\Tenant\AssetCategory;
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
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        Asset::find($this->deleteId)?->delete();
        $this->showDeleteModal = false;
        $this->deleteId = null;
        $this->toastSuccess(term_title('asset', 'Asset') . ' deleted.');
    }

    public function render()
    {
        $assets = Asset::with(['category'])
            ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->categoryFilter, fn($q) => $q->where('asset_category_id', $this->categoryFilter))
            ->orderBy('name')
            ->get();

        $categories = AssetCategory::orderBy('sort_order')->get();

        return view('livewire.tenant.assets.asset-list', compact('assets', 'categories'));
    }
}