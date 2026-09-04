<?php

namespace App\Livewire\Platform\Tenants;

use App\Models\Central\Tenant;
use App\Models\Tenant\User as TenantUser;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.platform')]
class TenantList extends Component
{
    use WithPagination, WithToast;

    public string $search   = '';
    public string $status   = '';
    public ?int   $viewing  = null; // tenant ID being viewed
    public bool   $showSuspendModal = false;
    public ?int   $suspendId = null;
    public bool   $showDeleteModal = false;
    public ?int   $deleteId = null;
    public string $deleteConfirmText = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function viewTenant(int $id): void
    {
        $this->viewing = $id;
    }

    public function closeTenant(): void
    {
        $this->viewing = null;
    }

    public function confirmSuspend(int $id): void
    {
        $this->suspendId       = $id;
        $this->showSuspendModal = true;
    }

    public function suspend(): void
    {
        $tenant = Tenant::find($this->suspendId);
        if ($tenant) {
            $tenant->update(['status' => 'suspended']);
            $this->toastWarning('Company suspended.');
        }
        $this->showSuspendModal = false;
        $this->suspendId        = null;
    }

    public function activate(int $id): void
    {
        $tenant = Tenant::find($id);
        if ($tenant) {
            $tenant->update(['status' => 'active']);
            $this->toastSuccess('Company activated.');
        }
    }

    public function cancelSuspend(): void
    {
        $this->showSuspendModal = false;
        $this->suspendId        = null;
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId          = $id;
        $this->deleteConfirmText = '';
        $this->showDeleteModal   = true;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal   = false;
        $this->deleteId          = null;
        $this->deleteConfirmText = '';
    }

    /**
     * Requires typing the company's exact name before deletion actually
     * proceeds — this is a genuinely irreversible action wiping an
     * entire tenant's data (every event, staff member, client, vendor,
     * task, moodboard, checklist — everything), so a plain "Yes/No"
     * confirmation isn't a strong enough safeguard for something this
     * destructive. Matches the same weight this action deserves at the
     * platform level.
     */
    public function delete(): void
    {
        $tenant = Tenant::find($this->deleteId);

        if (!$tenant) {
            $this->cancelDelete();
            return;
        }

        if ($this->deleteConfirmText !== $tenant->name) {
            $this->addError('deleteConfirmText', 'Company name does not match.');
            return;
        }

        TenantUser::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->delete();

        $tenant->delete();

        $this->toastSuccess('Company permanently deleted.');
        $this->showDeleteModal = false;
        $this->deleteId        = null;
        $this->deleteConfirmText = '';

        if ($this->viewing === $tenant->id) {
            $this->viewing = null;
        }
    }

    public function render()
    {
        $query = Tenant::query()
            ->when($this->search, fn($q) =>
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('slug', 'like', '%' . $this->search . '%')
            )
            ->when($this->status, fn($q) =>
                $q->where('status', $this->status)
            )
            ->latest();

        $viewingTenant = $this->viewing
            ? Tenant::with('plan')->find($this->viewing)
            : null;

        return view('livewire.platform.tenants.tenant-list', [
            'tenants'       => $query->paginate(15),
            'viewingTenant' => $viewingTenant,
        ]);
    }
}