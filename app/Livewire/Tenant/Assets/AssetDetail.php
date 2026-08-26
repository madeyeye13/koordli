<?php

namespace App\Livewire\Tenant\Assets;

use App\Models\Tenant\Asset;
use App\Models\Tenant\AssetEventAssignment;
use App\Models\Tenant\Event;
use App\Services\PermissionService;
use App\Traits\WithToast;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.tenant')]
class AssetDetail extends Component
{
    use WithToast;

    public Asset $asset;

    public bool   $showAssignForm = false;
    public ?int   $assign_event_id = null;
    public string $assign_date_from = '';
    public string $assign_date_to   = '';
    public string $assign_notes     = '';

    public bool $showDeleteAssignModal = false;
    public ?int $deleteAssignId = null;

    public function mount(int $id): void
    {
        abort_unless(
            app(PermissionService::class)->userCan(auth()->user(), 'assets.manage'),
            403
        );

        $this->asset = Asset::with(['category', 'eventAssignments.event'])->findOrFail($id);
    }

    public function showAssign(): void
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'assets.manage')) {
            $this->toastError('You do not have permission to manage assets.');
            return;
        }

        $this->reset(['assign_event_id', 'assign_date_from', 'assign_date_to', 'assign_notes']);
        $this->showAssignForm = true;
    }

    public function assignToEvent(): void
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'assets.manage')) {
            $this->toastError('You do not have permission to manage assets.');
            return;
        }

        $this->validate([
            'assign_event_id' => ['required', Rule::exists('events', 'id')->where('tenant_id', auth()->user()->tenant_id)],
        ]);

        $exists = AssetEventAssignment::where('asset_id', $this->asset->id)
            ->where('event_id', $this->assign_event_id)
            ->exists();

        if ($exists) {
            $this->addError('assign_event_id', 'This ' . term('asset', 'asset') . ' is already assigned to that ' . term('event', 'event') . '.');
            return;
        }

        AssetEventAssignment::create([
            'tenant_id'  => auth()->user()->tenant_id,
            'asset_id'   => $this->asset->id,
            'event_id'   => $this->assign_event_id,
            'date_from'  => $this->assign_date_from ?: null,
            'date_to'    => $this->assign_date_to ?: null,
            'notes'      => $this->assign_notes ?: null,
        ]);

        $this->asset->update(['status' => 'reserved']);
        $this->asset->load('eventAssignments.event');
        $this->showAssignForm = false;
        $this->toastSuccess(term_title('asset', 'Asset') . ' assigned.');
    }

    public function confirmDeleteAssign(int $id): void
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'assets.manage')) {
            $this->toastError('You do not have permission to manage assets.');
            return;
        }

        $this->deleteAssignId = $id;
        $this->showDeleteAssignModal = true;
    }

    public function deleteAssign(): void
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'assets.manage')) {
            $this->toastError('You do not have permission to manage assets.');
            $this->showDeleteAssignModal = false;
            return;
        }

        AssetEventAssignment::find($this->deleteAssignId)?->delete();

        if ($this->asset->eventAssignments()->count() === 0) {
            $this->asset->update(['status' => 'available']);
        }

        $this->asset->load('eventAssignments.event');
        $this->showDeleteAssignModal = false;
        $this->deleteAssignId = null;
        $this->toastSuccess('Assignment removed.');
    }

    public function updateStatus(string $status): void
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'assets.manage')) {
            $this->toastError('You do not have permission to manage assets.');
            return;
        }

        $this->asset->update(['status' => $status]);
        $this->asset->refresh();
        $this->toastSuccess('Status updated.');
    }

    public function render()
    {
        $availableEvents = Event::whereDoesntHave('assetAssignments', fn($q) =>
            $q->where('asset_id', $this->asset->id)
        )->orderByDesc('date')->get(['id', 'name', 'date']);

        return view('livewire.tenant.assets.asset-detail', compact('availableEvents'));
    }
}