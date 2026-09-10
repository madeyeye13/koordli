<?php

namespace App\Livewire\Platform\Staff;

use App\Traits\WithToast;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

#[Layout('layouts.platform')]
class PlatformRoleManager extends Component
{
    use WithToast;

    public bool $showRoleForm = false;
    public ?int $editingRoleId = null;
    public string $roleName = '';
    public array $selectedPermissions = [];

    private const SYSTEM_ROLES = ['platform_super_admin', 'platform_owner'];

    public function mount(): void
    {
        abort_unless(auth('platform')->user()?->can('platform.roles.manage'), 403);
    }

    public function newRole(): void
    {
        $this->reset(['editingRoleId', 'roleName', 'selectedPermissions']);
        $this->showRoleForm = true;
    }

    public function editRole(int $id): void
    {
        $role = Role::find($id);
        if (!$role || in_array($role->name, self::SYSTEM_ROLES)) {
            $this->toastError('This role cannot be edited.');
            return;
        }
        $this->editingRoleId = $id;
        $this->roleName = str_replace('platform_', '', $role->name);
        $this->selectedPermissions = $role->permissions->pluck('name')->toArray();
        $this->showRoleForm = true;
    }

    public function togglePermission(string $permission): void
    {
        $this->selectedPermissions = in_array($permission, $this->selectedPermissions)
            ? array_values(array_diff($this->selectedPermissions, [$permission]))
            : [...$this->selectedPermissions, $permission];
    }

    public function saveRole(): void
    {
        $this->validate(['roleName' => 'required|string|min:2|max:50']);

        $slug = 'platform_' . Str::slug($this->roleName, '_');

        if ($this->editingRoleId) {
            $role = Role::find($this->editingRoleId);
            $role->update(['name' => $slug]);
        } else {
            $role = Role::firstOrCreate(['name' => $slug, 'guard_name' => 'platform', 'tenant_id' => null]);
        }

        $role->syncPermissions(
            Permission::whereIn('name', $this->selectedPermissions)->where('guard_name', 'platform')->get()
        );

        $this->showRoleForm = false;
        $this->toastSuccess('Role saved.');
    }

    public function deleteRole(int $id): void
    {
        $role = Role::find($id);
        if (!$role || in_array($role->name, self::SYSTEM_ROLES)) {
            $this->toastError('This role cannot be deleted.');
            return;
        }
        if (\App\Models\Central\PlatformUser::role($role->name)->exists()) {
            $this->toastError('Cannot delete a role that still has staff assigned to it.');
            return;
        }
        $role->delete();
        $this->toastSuccess('Role deleted.');
    }

    public function render()
    {
        return view('livewire.platform.staff.platform-role-manager', [
            'roles' => Role::where('guard_name', 'platform')->withCount('users')->orderBy('name')->get(),
            'permissions' => Permission::where('guard_name', 'platform')->orderBy('name')->get(),
            'systemRoles' => self::SYSTEM_ROLES,
        ]);
    }
}