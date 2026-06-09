<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Set team_id to null for platform-level permissions
        app()[PermissionRegistrar::class]->setPermissionsTeamId(null);

        /*
        |----------------------------------------------------------------------
        | PLATFORM-LEVEL PERMISSIONS (no tenant context)
        |----------------------------------------------------------------------
        */
        $platformPermissions = [
            'platform.access',
            'platform.tenants.view',
            'platform.tenants.create',
            'platform.tenants.edit',
            'platform.tenants.delete',
            'platform.plans.manage',
            'platform.subscriptions.manage',
            'platform.features.manage',
            'platform.analytics.view',
            'platform.settings.manage',
        ];

        foreach ($platformPermissions as $permission) {
            Permission::firstOrCreate([
                'name'       => $permission,
                'guard_name' => 'platform',
            ]);
        }

        // Platform owner role
        $platformOwner = Role::firstOrCreate([
            'name'       => 'platform_owner',
            'guard_name' => 'platform',
            'tenant_id'  => null,
        ]);

        $platformOwner->givePermissionTo(
            Permission::where('guard_name', 'platform')->get()
        );

        /*
        |----------------------------------------------------------------------
        | TENANT-LEVEL PERMISSIONS (scoped per tenant via team_id)
        |----------------------------------------------------------------------
        */
        $tenantPermissions = [
            // Events
            'events.view',
            'events.create',
            'events.edit',
            'events.delete',

            // Tasks
            'tasks.view',
            'tasks.create',
            'tasks.edit',
            'tasks.delete',
            'tasks.assign',

            // Vendors
            'vendors.view',
            'vendors.create',
            'vendors.edit',
            'vendors.delete',

            // Budget
            'budget.view',
            'budget.manage',

            // Guests
            'guests.view',
            'guests.create',
            'guests.edit',
            'guests.delete',
            'guests.checkin',

            // RSVP
            'rsvp.view',
            'rsvp.manage',

            // Runsheet
            'runsheet.view',
            'runsheet.manage',

            // Documents
            'documents.view',
            'documents.upload',
            'documents.delete',

            // Staff
            'staff.view',
            'staff.invite',
            'staff.edit',
            'staff.remove',

            // Settings
            'settings.view',
            'settings.manage',

            // Client portal
            'client.portal.access',

            // Reports
            'reports.view',
        ];

        foreach ($tenantPermissions as $permission) {
            Permission::firstOrCreate([
                'name'       => $permission,
                'guard_name' => 'web',
            ]);
        }

        // We define ROLE TEMPLATES here (tenant_id = null)
        // When a tenant is created, DefaultTenantSeeder copies
        // these roles and assigns them with that tenant's ID

        $roles = [
            'company_owner' => $tenantPermissions, // all permissions
            'coordinator'   => [
                'events.view', 'events.create', 'events.edit',
                'tasks.view', 'tasks.create', 'tasks.edit', 'tasks.assign',
                'vendors.view', 'vendors.create', 'vendors.edit',
                'budget.view',
                'guests.view', 'guests.create', 'guests.edit', 'guests.checkin',
                'rsvp.view', 'rsvp.manage',
                'runsheet.view', 'runsheet.manage',
                'documents.view', 'documents.upload',
                'reports.view',
            ],
            'finance'       => [
                'events.view',
                'budget.view', 'budget.manage',
                'vendors.view',
                'reports.view',
            ],
            'operations'    => [
                'events.view',
                'tasks.view', 'tasks.edit',
                'vendors.view',
                'runsheet.view', 'runsheet.manage',
                'guests.view', 'guests.checkin',
            ],
            'social_media_manager' => [
                'events.view',
                'guests.view',
                'documents.view',
            ],
            'client'        => [
                'client.portal.access',
                'events.view',
                'budget.view',
                'documents.view',
                'rsvp.view',
            ],
        ];

        foreach ($roles as $roleName => $permissions) {
            $role = Role::firstOrCreate([
                'name'       => $roleName,
                'guard_name' => 'web',
                'tenant_id'  => null, // template role
            ]);

            $role->syncPermissions(
                Permission::whereIn('name', $permissions)
                    ->where('guard_name', 'web')
                    ->get()
            );
        }

        $this->command->info('Permissions and role templates seeded.');
    }
}