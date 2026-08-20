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

    public bool   $showRoleForm = false;
    public ?int   $editingRoleId = null;
    public string $roleName = '';

    public bool $showDeleteModal = false;
    public ?int $deleteRoleId = null;

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

    /**
     * Renderless: switching tabs is owned by Alpine on the client for
     * instant visual feedback (per this app's Instant UI rule). This just
     * keeps the server-side $selectedRoleId in sync in the background so
     * subsequent actions (togglePermission) operate on the right role —
     * it must never trigger a full re-render, or the tab switch delay
     * comes right back.
     */
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

    public function showCreateRole(): void
    {
        $this->editingRoleId = null;
        $this->roleName = '';
        $this->showRoleForm = true;
    }

    public function showRenameRole(int $roleId): void
    {
        $role = Role::where('id', $roleId)->where('tenant_id', $this->tenantId())->first();
        if (!$role || $role->is_system) return;

        $this->editingRoleId = $roleId;
        $this->roleName = $role->name;
        $this->showRoleForm = true;
    }

    public function saveRole(): void
    {
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

        $this->showRoleForm = false;
        $this->roleName = '';
        $this->editingRoleId = null;
    }

    public function cancelRoleForm(): void
    {
        $this->showRoleForm = false;
        $this->roleName = '';
        $this->editingRoleId = null;
    }

    public function confirmDeleteRole(int $roleId): void
    {
        $role = Role::where('id', $roleId)->where('tenant_id', $this->tenantId())->first();
        if (!$role || $role->is_system) return;

        $this->deleteRoleId = $roleId;
        $this->showDeleteModal = true;
    }

    public function deleteRole(): void
    {
        $tenantId = $this->tenantId();
        $role = Role::where('id', $this->deleteRoleId)->where('tenant_id', $tenantId)->first();

        if (!$role || $role->is_system) {
            $this->showDeleteModal = false;
            return;
        }

        $staffCount = DB::table('model_has_roles')
            ->where('role_id', $role->id)
            ->count();

        if ($staffCount > 0) {
            $this->toastError("This role has {$staffCount} staff member(s) assigned. Reassign them to a different role first.");
            $this->showDeleteModal = false;
            return;
        }

        $role->delete();

        if ($this->selectedRoleId === $this->deleteRoleId) {
            $this->selectedRoleId = Role::where('tenant_id', $tenantId)
                ->where('guard_name', 'web')
                ->orderByDesc('is_system')
                ->orderBy('name')
                ->first()?->id;
        }

        $this->showDeleteModal = false;
        $this->deleteRoleId = null;
        $this->toastSuccess('Role deleted.');
    }

    public function cancelDeleteRole(): void
    {
        $this->showDeleteModal = false;
        $this->deleteRoleId = null;
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
            // FIX: the pivot column is `tenant_id`, not `team_id` — this
            // tenant's PermissionSeeder configures team_foreign_key as
            // 'tenant_id', so that's the physical column name in
            // model_has_roles. Using 'team_id' silently matched zero rows.
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

        // Payload for Alpine: every role's data, embedded once on initial
        // load, so switching tabs afterward is a pure client-side operation
        // with zero server round-trip (matches this app's Instant UI rule).
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