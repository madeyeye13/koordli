<?php

namespace App\Services;

use App\Models\Tenant\StaffPermissionOverride;
use App\Models\Tenant\User;
use Spatie\Permission\PermissionRegistrar;

class PermissionService
{
    /**
     * The single canonical place "can this staff member do X" is ever
     * decided. Every permission check anywhere in the app should call
     * this rather than $user->hasRole() or $user->can() directly, so
     * the owner-bypass + override logic never has to be duplicated.
     *
     * Resolution order:
     *   1. company_owner (is_system role) → always true, no exceptions.
     *   2. Explicit per-user override exists → use it (grant OR revoke).
     *   3. No override → fall back to the user's role's default permissions.
     */
    public function userCan(User $user, string $permission): bool
    {
        app(PermissionRegistrar::class)->setPermissionsTeamId($user->tenant_id);

        if ($this->isSystemOwner($user)) {
            return true;
        }

        $override = StaffPermissionOverride::where('user_id', $user->id)
            ->where('permission_name', $permission)
            ->first();

        if ($override) {
            return $override->granted;
        }

        return $user->hasPermissionTo($permission, 'web');
    }

    public function isSystemOwner(User $user): bool
    {
        return $user->roles()->where('is_system', true)->exists();
    }

    /**
     * Set or clear an individual override for one staff member on top of
     * their role. Passing $granted = null removes the override entirely,
     * reverting that person back to their role's default for this permission.
     */
    public function setOverride(User $user, string $permission, ?bool $granted): void
    {
        if ($granted === null) {
            StaffPermissionOverride::where('user_id', $user->id)
                ->where('permission_name', $permission)
                ->delete();
            return;
        }

        StaffPermissionOverride::updateOrCreate(
            [
                'user_id'         => $user->id,
                'permission_name' => $permission,
            ],
            [
                'tenant_id' => $user->tenant_id,
                'granted'   => $granted,
            ]
        );
    }
}