<?php

namespace App\Console\Commands;

use App\Models\Central\Tenant;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class BackfillStaffRoleTemplates extends Command
{
    protected $signature = 'koordli:backfill-staff-role-templates';

    protected $description = 'One-time: seeds the 4 starter role templates '
        . '(coordinator, finance, operations, social_media_manager) into '
        . 'every existing tenant created before role management existed, '
        . 'and flags each tenant\'s existing company_owner role as is_system.';

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
            ->get()
            ->keyBy('name');

        if ($templateRoles->isEmpty()) {
            $this->error('No template roles found (tenant_id IS NULL). Run PermissionSeeder first.');
            return self::FAILURE;
        }

        $tenants = Tenant::all();
        $this->info("Backfilling role templates for {$tenants->count()} tenant(s)...");

        $created = 0;
        $flagged = 0;

        foreach ($tenants as $tenant) {
            app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->id);

            foreach ($templateRoles as $name => $template) {
                $existing = Role::where('name', $name)
                    ->where('guard_name', 'web')
                    ->where('tenant_id', $tenant->id)
                    ->first();

                if ($existing) {
                    // Role already exists for this tenant (most likely just
                    // company_owner, from before TenantService copied all 5
                    // templates). Fix is_system + sync permissions for
                    // company_owner only — never silently overwrite an
                    // owner's own customizations to coordinator/finance/etc
                    // if those somehow already exist from the old code path.
                    if ($name === 'company_owner' && !$existing->is_system) {
                        $existing->update(['is_system' => true]);
                        $existing->syncPermissions($template->permissions);
                        $flagged++;
                    }
                    continue;
                }

                // NOTE: Role::create() has a known Spatie bug where its
                // internal duplicate-name check does not correctly scope
                // by tenant_id in this package version, so it throws
                // RoleAlreadyExists against the NULL-tenant template row
                // even though no tenant-scoped row exists yet. firstOrCreate()
                // bypasses that buggy check (uses newModelInstance()->save()
                // internally instead of the overridden static create()) —
                // same reason TenantService.php already used firstOrCreate
                // rather than create() for this exact operation.
                $tenantRole = Role::firstOrCreate(
                    [
                        'name'       => $name,
                        'guard_name' => 'web',
                        'tenant_id'  => $tenant->id,
                    ],
                    [
                        'is_system' => $name === 'company_owner',
                    ]
                );

                $tenantRole->syncPermissions($template->permissions);
                $created++;
            }
        }

        app(PermissionRegistrar::class)->setPermissionsTeamId(null);

        $this->info("Done. Created {$created} new role row(s), flagged {$flagged} existing company_owner role(s) as is_system.");
        return self::SUCCESS;
    }
}