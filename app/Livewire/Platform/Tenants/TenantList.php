<?php

namespace App\Livewire\Platform\Tenants;

use App\Models\Central\Tenant;
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