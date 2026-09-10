<?php

namespace App\Livewire\Platform\Staff;

use App\Mail\PlatformStaffInviteMail;
use App\Mail\PlatformRoleChangedMail;
use App\Models\Central\PlatformUser;
use App\Traits\WithToast;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Permission\Models\Role;

#[Layout('layouts.platform')]
class PlatformStaffManager extends Component
{
    use WithToast;

    public bool $showInviteForm = false;
    public string $newName = '';
    public string $newEmail = '';
    public string $newRole = 'platform_support_agent';

    public bool $showRoleModal = false;
    public ?int $editingUserId = null;
    public string $editingRole = '';

    public bool $showDeactivateModal = false;
    public ?int $targetUserId = null;
    public bool $targetIsActive = false;

    public bool $showDeleteModal = false;
    public ?int $deleteUserId = null;

    public bool $showEmailEditModal = false;
    public ?int $emailEditUserId = null;
    public string $emailEditValue = '';

    public function openEmailEdit(int $userId): void
    {
        $user = PlatformUser::find($userId);
        if (!$user) return;
        $this->emailEditUserId = $userId;
        $this->emailEditValue = $user->email;
        $this->showEmailEditModal = true;
    }

    public function saveEmailEdit(): void
    {
        $this->validate([
            'emailEditValue' => 'required|email|unique:platform_users,email,' . $this->emailEditUserId,
        ]);

        PlatformUser::find($this->emailEditUserId)?->update(['email' => $this->emailEditValue]);

        $this->showEmailEditModal = false;
        $this->toastSuccess('Email updated.');
    }

    public function mount(): void
    {
        abort_unless(auth('platform')->user()?->can('platform.staff.manage'), 403);
    }

    public function openInviteForm(): void
    {
        $this->showInviteForm = true;
    }

    public function closeInviteForm(): void
    {
        $this->showInviteForm = false;
        $this->reset(['newName', 'newEmail', 'newRole']);
    }

    public function inviteStaff(): void
    {
        $this->validate([
            'newName'  => 'required|string|min:2|max:150',
            'newEmail' => 'required|email|unique:platform_users,email',
            'newRole'  => 'required|string',
        ]);

        $user = PlatformUser::create([
            'uuid'         => Str::uuid(),
            'name'         => $this->newName,
            'email'        => $this->newEmail,
            'password'     => bcrypt(Str::random(32)), // unusable until they set their own via the invite link
            'role'         => $this->newRole,
            'is_active'    => true,
            'invite_token' => Str::random(48),
            'invited_at'   => now(),
            'invited_by'   => auth('platform')->id(),
        ]);

        $user->assignRole($this->newRole);

        Mail::to($user->email)->send(new PlatformStaffInviteMail($user, auth('platform')->user()->name));
        $user->notify(new \App\Notifications\PlatformStaffInvitedNotification(auth('platform')->user()->name, $this->newRole));

        $this->closeInviteForm();
        $this->toastSuccess('Invitation sent to ' . $user->email . '.');
    }

    public function openRoleModal(int $userId): void
    {
        $user = PlatformUser::find($userId);
        if (!$user) return;
        $this->editingUserId = $userId;
        $this->editingRole = $user->roles->first()?->name ?? 'platform_support_agent';
        $this->showRoleModal = true;
    }

    public function saveRole(): void
    {
        $user = PlatformUser::find($this->editingUserId);
        if (!$user) return;

        if ($user->id === auth('platform')->id() && $this->editingRole !== 'platform_owner' && $user->hasRole('platform_owner')) {
            $this->toastError('You cannot remove your own Owner role.');
            return;
        }

        $oldRole = $user->roles->first()?->name;
        $user->syncRoles([$this->editingRole]);
        $user->update(['role' => $this->editingRole]);

        if ($oldRole !== $this->editingRole) {
            Mail::to($user->email)->send(new PlatformRoleChangedMail($user, $this->editingRole));
            $user->notify(new \App\Notifications\PlatformRoleChangedNotification($this->editingRole));
        }

        $this->showRoleModal = false;
        $this->editingUserId = null;
        $this->editingRole = '';
        $this->toastSuccess('Role updated.');
    }

    public function closeRoleModal(): void
    {
        $this->showRoleModal = false;
        $this->editingUserId = null;
        $this->editingRole = '';
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deleteUserId = null;
    }

    public function confirmDeleteStaff(int $userId): void
    {
        if ($userId === auth('platform')->id()) {
            $this->toastError('You cannot delete your own account.');
            return;
        }

        $this->deleteUserId = $userId;
        $this->showDeleteModal = true;
    }

    public function deleteStaff(?int $userId = null): void
    {
        $targetUserId = $userId ?? $this->deleteUserId;
        $user = PlatformUser::find($targetUserId);

        if (!$user || $user->id === auth('platform')->id()) {
            $this->toastError('You cannot delete your own account.');
            $this->showDeleteModal = false;
            $this->deleteUserId = null;
            return;
        }

        $user->roles()->detach();
        $user->delete();

        $this->closeDeleteModal();
        $this->toastSuccess('Staff member deleted.');
    }

    public function confirmToggleActive(int $userId, bool $isActive): void
    {
        if ($userId === auth('platform')->id()) {
            $this->toastError('You cannot deactivate your own account.');
            return;
        }

        $this->targetUserId = $userId;
        $this->targetIsActive = $isActive;
        $this->showDeactivateModal = true;
    }

    public function cancelToggle(): void
    {
        $this->showDeactivateModal = false;
        $this->targetUserId = null;
        $this->targetIsActive = false;
    }

    public function toggleActive(?int $userId = null): void
    {
        $targetUserId = $userId ?? $this->targetUserId;
        $user = PlatformUser::find($targetUserId);

        if (!$user || $user->id === auth('platform')->id()) {
            $this->toastError('You cannot deactivate your own account.');
            $this->cancelToggle();
            return;
        }

        $user->update(['is_active' => !$user->is_active]);
        $this->cancelToggle();
        $this->toastSuccess($user->is_active ? 'Account activated.' : 'Account deactivated.');
    }

    public function render()
    {
        return view('livewire.platform.staff.platform-staff-manager', [
            'staff' => PlatformUser::with('roles')->orderBy('name')->get(),
            'roles' => Role::where('guard_name', 'platform')->orderBy('name')->get(),
        ]);
    }
}