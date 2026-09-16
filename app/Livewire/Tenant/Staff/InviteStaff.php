<?php

namespace App\Livewire\Tenant\Staff;

use App\Models\Tenant\StaffPermissionOverride;
use App\Models\Tenant\User;
use App\Services\PermissionService;
use App\Traits\WithToast;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

#[Layout('layouts.tenant')]
class InviteStaff extends Component
{
    use WithToast;

    // Same exclusion list as RolePermissions.php — Client/Vendor guard
    // permissions don't apply to staff `web`-guard roles or overrides.
    private const EXCLUDED_PREFIXES = [
        'vendor.profile', 'vendor.events', 'vendor.runsheet', 'vendor.payments',
        'client.portal',
    ];

    public string $name           = '';
    public string $email          = '';
    public ?int   $selectedRoleId = null;
    public string $password       = '';

    public ?int  $editId   = null;
    public ?User $editUser = null;

    public bool $showStaffLimitModal = false;
    public int $staffLimit = 0;
    public int $currentStaffCount = 0;

    private function tenantId(): int
    {
        return auth()->user()->tenant_id;
    }

    public function mount(?int $id = null): void
    {
        abort_unless(
            app(PermissionService::class)->userCan(auth()->user(), $id ? 'staff.edit' : 'staff.invite'),
            403
        );

        app(PermissionRegistrar::class)->setPermissionsTeamId($this->tenantId());

        if ($id) {
            $this->editId         = $id;
            $this->editUser       = User::with('roles')->findOrFail($id);
            $this->name           = $this->editUser->name;
            $this->email          = $this->editUser->email;
            $this->selectedRoleId = $this->editUser->roles->first()?->id;
        } else {
            $this->password = 'Koordli@' . rand(1000, 9999);
        }
    }

    public function save(): void
    {
        if ($this->editUser) {
            $this->update();
        } else {
            $this->invite();
        }
    }

    protected function invite(): void
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'staff.invite')) {
            $this->toastError('You do not have permission to invite staff.');
            return;
        }

        $tenantId = $this->tenantId();
        $tenant = auth()->user()->tenant;
        $this->staffLimit = app(\App\Services\FeatureGateService::class)->getLimit($tenant, 'max_staff');
        $this->currentStaffCount = User::where('tenant_id', $tenantId)->where('type', 'staff')->count();

        if ($this->staffLimit > 0 && $this->currentStaffCount >= $this->staffLimit) {
            $this->showStaffLimitModal = true;
            return;
        }

        $this->validate([
            'name'           => 'required|string|min:2|max:100',
            'email'          => 'required|email',
            'selectedRoleId' => 'required|exists:roles,id,tenant_id,' . $tenantId,
        ]);

        $exists = User::where('email', $this->email)->exists();
        if ($exists) {
            $this->addError('email', 'This email is already registered in your workspace.');
            return;
        }

        $role = Role::where('id', $this->selectedRoleId)->where('tenant_id', $tenantId)->first();
        if (!$role) {
            $this->addError('selectedRoleId', 'Please select a valid role.');
            return;
        }

        $user = User::create([
            'uuid'                 => Str::uuid(),
            'tenant_id'            => $tenantId,
            'name'                 => $this->name,
            'email'                => $this->email,
            'password'             => Hash::make($this->password),
            'type'                 => 'staff',
            'is_active'            => true,
            'is_self_registered'   => false,
            'onboarding_completed' => false,
        ]);

        app(PermissionRegistrar::class)->setPermissionsTeamId($tenantId);
        $user->assignRole($role);

        \App\Jobs\SendStaffInviteJob::dispatch(
            staffEmail:   $this->email,
            staffName:    $this->name,
            tempPassword: $this->password,
            companyName:  auth()->user()->tenant->name,
            inviterName:  auth()->user()->name,
            whiteLabel:   app(\App\Services\FeatureGateService::class)->canAccess(auth()->user()->tenant, 'white_label'),
        );

        $this->toastSuccess("{$this->name} has been invited. Login credentials sent to {$this->email}.");
        $this->redirect(route('tenant.staff'), navigate: true);
    }

    protected function update(): void
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'staff.edit')) {
            $this->toastError('You do not have permission to edit staff.');
            return;
        }

        $tenantId = $this->tenantId();

        $this->validate([
            'name'           => 'required|string|min:2|max:100',
            'selectedRoleId' => 'required|exists:roles,id,tenant_id,' . $tenantId,
        ]);

        $role = Role::where('id', $this->selectedRoleId)->where('tenant_id', $tenantId)->first();
        if (!$role) {
            $this->addError('selectedRoleId', 'Please select a valid role.');
            return;
        }

        $currentRoleIsSystem = $this->editUser->roles->first()?->is_system ?? false;
        if ($currentRoleIsSystem && !$role->is_system) {
            $ownerCount = User::where('tenant_id', $tenantId)
                ->whereHas('roles', fn($q) => $q->where('is_system', true))
                ->count();

            if ($ownerCount <= 1) {
                $this->addError('selectedRoleId', 'You cannot change the last remaining Company Owner\'s role.');
                return;
            }
        }

        $this->editUser->update(['name' => $this->name]);

        app(PermissionRegistrar::class)->setPermissionsTeamId($tenantId);
        $this->editUser->syncRoles([$role]);

        if ($this->password) {
            $this->editUser->update(['password' => Hash::make($this->password)]);
        }

        $this->toastSuccess('Staff member updated.');
        $this->redirect(route('tenant.staff'), navigate: true);
    }

    /**
     * Renderless: mirrors RolePermissions::togglePermission — the checkbox
     * flips instantly via Alpine's optimistic local state, this just
     * persists the resulting override (or clears it, if the toggle brought
     * the permission back in line with the role's own default) in the
     * background with zero visible delay.
     */
    #[Renderless]
    public function toggleOverride(string $permissionName): void
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'staff.roles.manage')) return;
        if (!$this->editUser) return;

        $tenantId = $this->tenantId();
        app(PermissionRegistrar::class)->setPermissionsTeamId($tenantId);

        $role = $this->editUser->roles->first();
        if (!$role || $role->is_system) return; // owner is always full access, no overrides possible

        $roleDefault = $role->hasPermissionTo($permissionName, 'web');

        $existingOverride = StaffPermissionOverride::where('user_id', $this->editUser->id)
            ->where('permission_name', $permissionName)
            ->first();

        $currentEffective = $existingOverride ? (bool) $existingOverride->granted : $roleDefault;
        $desired = !$currentEffective;

        // If the desired state now matches the role's own default, clear
        // the override entirely rather than storing a redundant row —
        // matches PermissionService::setOverride(..., null) semantics.
        app(PermissionService::class)->setOverride(
            $this->editUser,
            $permissionName,
            $desired === $roleDefault ? null : $desired
        );
    }

    private function groupedPermissions()
    {
        $all = Permission::where('guard_name', 'web')
            ->orderBy('name')
            ->get()
            ->reject(function ($perm) {
                foreach (self::EXCLUDED_PREFIXES as $prefix) {
                    if (str_starts_with($perm->name, $prefix)) return true;
                }
                return false;
            });

        return $all->groupBy(function ($perm) {
            $key = explode('.', $perm->name)[0];
            return str($key)->replace('-', ' ')->title();
        });
    }

    public function render()
    {
        $tenantId = $this->tenantId();
        app(PermissionRegistrar::class)->setPermissionsTeamId($tenantId);

        $roles = Role::with('permissions')
            ->where('tenant_id', $tenantId)
            ->where('guard_name', 'web')
            ->orderByDesc('is_system')
            ->orderBy('name')
            ->get();

        $canManageOverrides    = false;
        $editedUserRoleIsSystem = false;
        $overridesDataJson     = null;

        if ($this->editUser) {
            $canManageOverrides = app(PermissionService::class)->userCan(auth()->user(), 'staff.roles.manage');
            $role = $this->editUser->roles->first();
            $editedUserRoleIsSystem = $role?->is_system ?? false;

            if ($canManageOverrides && $role && !$editedUserRoleIsSystem) {
                $rolePermissionNames = $role->permissions->pluck('name')->values();

                $overrides = StaffPermissionOverride::where('user_id', $this->editUser->id)
                    ->get()
                    ->mapWithKeys(fn($o) => [$o->permission_name => (bool) $o->granted]);

                $overridesDataJson = json_encode([
                    'rolePermissions' => $rolePermissionNames,
                    'overrides'       => $overrides,
                ]);
            }
        }

        return view('livewire.tenant.staff.invite-staff', [
            'roles'                  => $roles,
            'selectedRoleLabel'      => $roles->firstWhere('id', $this->selectedRoleId)?->name,
            'groupedPermissions'     => $this->groupedPermissions(),
            'canManageOverrides'     => $canManageOverrides,
            'editedUserRoleIsSystem' => $editedUserRoleIsSystem,
            'overridesDataJson'      => $overridesDataJson,
        ]);
    }
}