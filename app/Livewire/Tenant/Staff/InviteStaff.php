<?php

namespace App\Livewire\Tenant\Staff;

use App\Models\Tenant\User;
use App\Traits\WithToast;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Permission\Models\Role;

#[Layout('layouts.tenant')]
class InviteStaff extends Component
{
    use WithToast;

    public string $name     = '';
    public string $email    = '';
    public string $role     = 'staff';
    public string $password = '';

    public ?int  $editId   = null;
    public ?User $editUser = null;

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->editId   = $id;
            $this->editUser = User::with('roles')->findOrFail($id);
            $this->name     = $this->editUser->name;
            $this->email    = $this->editUser->email;
            $this->role     = $this->editUser->roles->first()?->name ?? 'staff';
        } else {
            // Generate a default password
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
        $this->validate([
            'name'  => 'required|string|min:2|max:100',
            'email' => 'required|email',
            'role'  => 'required|in:company_owner,manager,staff',
        ]);

        $exists = User::where('email', $this->email)->exists();
        if ($exists) {
            $this->addError('email', 'This email is already registered in your workspace.');
            return;
        }

        $tenantId = auth()->user()->tenant_id;

        $user = User::create([
            'uuid'                 => \Illuminate\Support\Str::uuid(),
            'tenant_id'            => $tenantId,
            'name'                 => $this->name,
            'email'                => $this->email,
            'password'             => \Illuminate\Support\Facades\Hash::make($this->password),
            'type'                 => 'staff',
            'is_active'            => true,
            'is_self_registered'   => false,
            'onboarding_completed' => false,
        ]);

        // Set team context BEFORE assigning role
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($tenantId);

        $role = \Spatie\Permission\Models\Role::firstOrCreate([
            'name'       => $this->role,
            'guard_name' => 'web',
            'tenant_id'  => $tenantId,
        ]);

        $user->assignRole($role);

        // Send invite email
        \App\Jobs\SendStaffInviteJob::dispatch(
            staffEmail:   $this->email,
            staffName:    $this->name,
            tempPassword: $this->password,
            companyName:  auth()->user()->tenant->name,
            inviterName:  auth()->user()->name,
        );

        $this->toastSuccess("{$this->name} has been invited. Login credentials sent to {$this->email}.");
        $this->redirect(route('tenant.staff'), navigate: true);
    }

    protected function update(): void
    {
        $this->validate([
            'name' => 'required|string|min:2|max:100',
            'role' => 'required|in:company_owner,manager,staff',
        ]);

        $tenantId = auth()->user()->tenant_id;

        $this->editUser->update(['name' => $this->name]);

        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($tenantId);

        $role = \Spatie\Permission\Models\Role::firstOrCreate([
            'name'       => $this->role,
            'guard_name' => 'web',
            'tenant_id'  => $tenantId,
        ]);

        $this->editUser->syncRoles([$role]);

        if ($this->password) {
            $this->editUser->update(['password' => \Illuminate\Support\Facades\Hash::make($this->password)]);
        }

        $this->toastSuccess('Staff member updated.');
        $this->redirect(route('tenant.staff'), navigate: true);
    }

    public function render()
    {
        return view('livewire.tenant.staff.invite-staff');
    }
}