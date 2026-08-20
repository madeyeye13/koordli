<?php

namespace App\Livewire\Tenant\Assets;

use App\Models\Tenant\Asset;
use App\Models\Tenant\AssetCategory;
use App\Services\PermissionService;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.tenant')]
class CreateAsset extends Component
{
    use WithToast;

    public ?Asset $asset = null;
    public bool   $isEdit = false;

    public string $name = '';
    public ?int   $asset_category_id = null;
    public string $status = 'available';
    public string $notes = '';

    public function mount(?Asset $asset = null): void
    {
        abort_unless(
            app(PermissionService::class)->userCan(auth()->user(), 'assets.manage'),
            403
        );

        if ($asset && $asset->exists) {
            $this->isEdit = true;
            $this->asset  = $asset;
            $this->name   = $asset->name;
            $this->asset_category_id = $asset->asset_category_id;
            $this->status = $asset->status;
            $this->notes  = $asset->notes ?? '';
        }
    }

    public function save(): void
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'assets.manage')) {
            $this->toastError('You do not have permission to manage assets.');
            return;
        }

        $this->validate([
            'name'   => 'required|string|min:2|max:150',
            'status' => 'required|in:available,reserved,maintenance',
        ]);

        $data = [
            'tenant_id'         => auth()->user()->tenant_id,
            'name'              => $this->name,
            'asset_category_id' => $this->asset_category_id ?: null,
            'status'            => $this->status,
            'notes'             => $this->notes ?: null,
        ];

        if ($this->isEdit) {
            $this->asset->update($data);
            $this->toastSuccess(term_title('asset', 'Asset') . ' updated.');
        } else {
            Asset::create($data);
            $this->toastSuccess(term_title('asset', 'Asset') . ' created.');
        }

        $this->redirect(route('tenant.assets'), navigate: true);
    }

    public function render()
    {
        $categories = AssetCategory::orderBy('sort_order')->get();
        return view('livewire.tenant.assets.create-asset', compact('categories'));
    }
}