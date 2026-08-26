<?php

namespace App\Livewire\Tenant\Staff;

use App\Services\PermissionService;
use App\Traits\WithToast;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

#[Layout('layouts.tenant')]
class RolePermissions extends Component
{
    use WithToast;

    private const EXCLUDED_PREFIXES = [
        'vendor.profile', 'vendor.events', 'vendor.runsheet', 'vendor.payments',
        'client.portal',
    ];

    public ?int $selectedRoleId = null;

    // Working state only for saveRole()/deleteRole() — modal open/close is
    // now owned entirely by Alpine for instant response, so these are no
    // longer read by the Blade view to control visibility.
    public ?int $editingRoleId = null;
    public string $roleName = '';

    private function tenantId(): int
    {
        return auth()->user()->tenant_id;
    }

    public function mount(): void
    {
        abort_unless(
            app(PermissionService::class)->userCan(auth()->user(), 'staff.roles.manage'),
            403
        );

        app(PermissionRegistrar::class)->setPermissionsTeamId($this->tenantId());

        $firstRole = Role::where('tenant_id', $this->tenantId())
            ->where('guard_name', 'web')
            ->orderByDesc('is_system')
            ->orderBy('name')
            ->first();

        $this->selectedRoleId = $firstRole?->id;
    }

    #[Renderless]
    public function selectRole(int $roleId): void
    {
        $this->selectedRoleId = $roleId;
    }

    #[Renderless]
    public function togglePermission(string $permissionName): void
    {
        $role = $this->currentRole();
        if (!$role || $role->is_system) return;

        app(PermissionRegistrar::class)->setPermissionsTeamId($this->tenantId());

        if ($role->hasPermissionTo($permissionName, 'web')) {
            $role->revokePermissionTo($permissionName);
        } else {
            $role->givePermissionTo($permissionName);
        }
    }

    /**
     * Called via $wire.saveRole(...) directly from Alpine — the modal that
     * collects the role id/name is Alpine-owned for instant open/close, so
     * this receives the values as parameters instead of reading properties
     * that used to be set by a wire:click open handler.
     */
    public function saveRole(?int $roleId, string $name): void
    {
        $this->editingRoleId = $roleId;
        $this->roleName = $name;

        $this->validate([
            'roleName' => 'required|string|min:2|max:60',
        ]);

        $tenantId = $this->tenantId();
        app(PermissionRegistrar::class)->setPermissionsTeamId($tenantId);

        if ($this->editingRoleId) {
            $role = Role::where('id', $this->editingRoleId)->where('tenant_id', $tenantId)->first();
            if (!$role || $role->is_system) {
                $this->toastError('This role cannot be renamed.');
                return;
            }

            $duplicate = Role::where('tenant_id', $tenantId)
                ->where('guard_name', 'web')
                ->where('name', $this->roleName)
                ->where('id', '!=', $role->id)
                ->exists();

            if ($duplicate) {
                $this->addError('roleName', 'A role with this name already exists.');
                return;
            }

            $role->update(['name' => $this->roleName]);
            $this->toastSuccess('Role renamed.');
        } else {
            $duplicate = Role::where('tenant_id', $tenantId)
                ->where('guard_name', 'web')
                ->where('name', $this->roleName)
                ->exists();

            if ($duplicate) {
                $this->addError('roleName', 'A role with this name already exists.');
                return;
            }

            $role = Role::create([
                'name' => $this->roleName,
                'guard_name' => 'web',
                'tenant_id' => $tenantId,
                'is_system' => false,
            ]);

            $this->selectedRoleId = $role->id;
            $this->toastSuccess('Role created. Set its permissions below.');
        }

        $this->roleName = '';
        $this->editingRoleId = null;
    }

    /**
     * Same pattern as saveRole() — called via $wire.deleteRole(id) from
     * Alpine, receiving the id directly instead of via a bound property.
     */
    public function deleteRole(int $roleId): void
    {
        $tenantId = $this->tenantId();
        $role = Role::where('id', $roleId)->where('tenant_id', $tenantId)->first();

        if (!$role || $role->is_system) {
            return;
        }

        $staffCount = DB::table('model_has_roles')
            ->where('role_id', $role->id)
            ->count();

        if ($staffCount > 0) {
            $this->toastError("This role has {$staffCount} staff member(s) assigned. Reassign them to a different role first.");
            return;
        }

        $role->delete();

        if ($this->selectedRoleId === $roleId) {
            $this->selectedRoleId = Role::where('tenant_id', $tenantId)
                ->where('guard_name', 'web')
                ->orderByDesc('is_system')
                ->orderBy('name')
                ->first()?->id;
        }

        $this->toastSuccess('Role deleted.');
    }

    private function currentRole(): ?Role
    {
        if (!$this->selectedRoleId) return null;
        return Role::where('id', $this->selectedRoleId)->where('tenant_id', $this->tenantId())->first();
    }

    public function render()
    {
        $tenantId = $this->tenantId();
        app(PermissionRegistrar::class)->setPermissionsTeamId($tenantId);

        $roles = Role::where('tenant_id', $tenantId)
            ->where('guard_name', 'web')
            ->with('permissions')
            ->withCount(['users' => function ($q) use ($tenantId) {
                $q->where('model_has_roles.tenant_id', $tenantId);
            }])
            ->orderByDesc('is_system')
            ->orderBy('name')
            ->get();

        $allPermissions = Permission::where('guard_name', 'web')
            ->orderBy('name')
            ->get()
            ->reject(function ($perm) {
                foreach (self::EXCLUDED_PREFIXES as $prefix) {
                    if (str_starts_with($perm->name, $prefix)) return true;
                }
                return false;
            });

        $grouped = $allPermissions->groupBy(function ($perm) {
            $key = explode('.', $perm->name)[0];
            return str($key)->replace('-', ' ')->title();
        });

        $rolesData = $roles->mapWithKeys(function ($role) {
            return [
                $role->id => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'isSystem' => (bool) $role->is_system,
                    'permissions' => $role->permissions->pluck('name')->values(),
                ],
            ];
        });

        return view('livewire.tenant.staff.role-permissions', [
            'roles' => $roles,
            'groupedPermissions' => $grouped,
            'rolesDataJson' => $rolesData->toJson(),
            'initialSelectedRoleId' => $this->selectedRoleId,
        ]);
    }
}