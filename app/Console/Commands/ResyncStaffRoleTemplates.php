<?php

namespace App\Console\Commands;

use App\Models\Central\Tenant;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class ResyncStaffRoleTemplates extends Command
{
    protected $signature = 'koordli:resync-staff-role-templates';

    protected $description = 'Additively grants newly-added template permissions '
        . '(e.g. contracts.manage, invoices.manage, assets.manage) to every '
        . 'existing tenant\'s matching starter role. Never removes permissions '
        . '— safe to run after any PermissionSeeder update, even if an owner '
        . 'has already customized their roles.';

    private const STAFF_ROLE_TEMPLATES = [
        'company_owner',
        'coordinator',
        'finance',
        'operations',
        'social_media_manager',
    ];

    public function handle(): int
    {
        $templateRoles = Role::where('guard_name', 'web')
            ->whereNull('tenant_id')
            ->whereIn('name', self::STAFF_ROLE_TEMPLATES)
            ->with('permissions')
            ->get()
            ->keyBy('name');

        if ($templateRoles->isEmpty()) {
            $this->error('No template roles found. Run PermissionSeeder first.');
            return self::FAILURE;
        }

        $tenants = Tenant::all();
        $this->info("Resyncing role templates for {$tenants->count()} tenant(s)...");

        $grantedTotal = 0;

        foreach ($tenants as $tenant) {
            app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->id);

            foreach ($templateRoles as $name => $template) {
                $tenantRole = Role::where('name', $name)
                    ->where('guard_name', 'web')
                    ->where('tenant_id', $tenant->id)
                    ->first();

                if (!$tenantRole) {
                    // Shouldn't happen if the earlier backfill ran, but skip
                    // gracefully rather than error if a tenant is missing a role.
                    continue;
                }

                $templatePermissionNames = $template->permissions->pluck('name');
                $currentPermissionNames  = $tenantRole->permissions->pluck('name');
                $missing = $templatePermissionNames->diff($currentPermissionNames);

                if ($missing->isNotEmpty()) {
                    $tenantRole->givePermissionTo($missing->values()->all());
                    $grantedTotal += $missing->count();
                    $this->line("  {$tenant->name} / {$name}: +{$missing->count()} permission(s)");
                }
            }
        }

        app(PermissionRegistrar::class)->setPermissionsTeamId(null);

        $this->info("Done. Granted {$grantedTotal} new permission assignment(s) across all tenants.");
        return self::SUCCESS;
    }
}