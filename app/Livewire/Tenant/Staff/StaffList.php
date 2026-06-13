<?php

namespace App\Livewire\Tenant\Staff;

use App\Models\Tenant\User;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.tenant')]
class StaffList extends Component
{
    use WithPagination, WithToast;

    #[Url]
    public string $search = '';

    #[Url]
    public string $roleFilter = '';

    #[Url]
    public string $statusFilter = '';

    public bool $showDeactivateModal = false;
    public ?int $targetUserId        = null;
    public bool $targetIsActive      = false;

    protected int $tenantId;

    public function mount(): void
    {
        $this->tenantId = auth()->user()->tenant_id;
        setPermissionsTeamId($this->tenantId);
    }

    public function updatedSearch(): void       { $this->resetPage(); }
    public function updatedRoleFilter(): void   { $this->resetPage(); }
    public function updatedStatusFilter(): void { $this->resetPage(); }

    public function confirmToggleActive(int $userId, bool $isActive): void
    {
        $this->targetUserId        = $userId;
        $this->targetIsActive      = $isActive;
        $this->showDeactivateModal = true;
    }

    public function toggleActive(): void
    {
        setPermissionsTeamId(auth()->user()->tenant_id);

        $user = User::find($this->targetUserId);

        if (!$user) {
            $this->showDeactivateModal = false;
            return;
        }

        if ($user->id === auth()->id()) {
            $this->toastError('You cannot deactivate your own account.');
            $this->showDeactivateModal = false;
            return;
        }

        $user->update(['is_active' => !$user->is_active]);
        $this->toastSuccess($user->is_active ? 'Staff member activated.' : 'Staff member deactivated.');
        $this->showDeactivateModal = false;
        $this->targetUserId = null;
    }

    public function cancelToggle(): void
    {
        $this->showDeactivateModal = false;
        $this->targetUserId = null;
    }

    public function render()
{
    $tenantId = auth()->user()->tenant_id;
    setPermissionsTeamId($tenantId);

    $staff = User::with('roles')
        ->where('tenant_id', $tenantId)
        ->where('type', 'staff')
        ->when($this->search, fn($q) =>
            $q->where('name', 'like', '%' . $this->search . '%')
              ->orWhere('email', 'like', '%' . $this->search . '%')
        )
        ->when($this->statusFilter !== '', fn($q) =>
            $q->where('is_active', $this->statusFilter === 'active')
        )
        ->when($this->roleFilter, fn($q) =>
    $q->whereHas('roles', fn($r) =>
        $r->where('name', $this->roleFilter)
          ->where(fn($r2) =>
              $r2->where('model_has_roles.team_id', $tenantId)
                 ->orWhereNull('model_has_roles.team_id')
          )
    )
)
        ->orderBy('name')
        ->paginate(15);

    return view('livewire.tenant.staff.staff-list', [
        'staff' => $staff,
    ]);
}
}