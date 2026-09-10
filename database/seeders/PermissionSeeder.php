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

            // Platform staff & roles — the actual missing piece per the audit
            'platform.staff.view',
            'platform.staff.manage',   // invite/edit/deactivate platform users
            'platform.roles.manage',   // create custom roles, assign permissions — Super Admin/Owner territory by default

            // Support module — previously ungoverned by any permission at all
            'support.tickets.view',
            'support.tickets.manage',   // respond, resolve, reassign
            'support.tickets.handoff',
            'support.faqs.view',
            'support.faqs.manage',
            'support.agents.manage',    // view/toggle OTHER agents' availability, not just your own
            'support.tickets.delete',   // permanent, destructive — deliberately separate from manage
        ];

        foreach ($platformPermissions as $permission) {
            Permission::firstOrCreate([
                'name'       => $permission,
                'guard_name' => 'platform',
            ]);
        }

        // Platform role definitions. super_admin and owner intentionally
        // receive every permission — see the note in the assistant's reply
        // for the stated assumption about their relationship; correct this
        // seeder if that assumption is wrong.
        $platformRoles = [
            'platform_super_admin' => $platformPermissions, // everything, always
            'platform_owner'       => $platformPermissions, // everything, always

            'platform_admin' => [
                'platform.access',
                'platform.tenants.view', 'platform.tenants.create', 'platform.tenants.edit',
                'platform.plans.manage', 'platform.subscriptions.manage', 'platform.features.manage',
                'platform.analytics.view', 'platform.settings.manage',
                'platform.staff.view',
                'support.tickets.view', 'support.tickets.manage', 'support.tickets.handoff', 'support.tickets.delete',
                'support.faqs.view', 'support.faqs.manage', 'support.agents.manage',
                // Deliberately withheld: platform.tenants.delete, platform.staff.manage,
                // platform.roles.manage — destructive/organizational actions stay with
                // Owner/Super Admin only by default.
            ],

            'platform_tech' => [
                'platform.access',
                'platform.tenants.view',
                'platform.features.manage',
                'platform.analytics.view',
                'platform.settings.manage',
                // Technical/system-facing access — deliberately excludes staff
                // management, role management, and tenant billing/deletion.
            ],

            'platform_support_manager' => [
                'platform.access',
                'support.tickets.view', 'support.tickets.manage', 'support.tickets.handoff', 'support.tickets.delete',
                'support.faqs.view', 'support.faqs.manage',
                'support.agents.manage', // can see/manage the whole support team's availability
            ],

            'platform_support_agent' => [
                'platform.access',
                'support.tickets.view', 'support.tickets.manage', // own assigned tickets
                'support.faqs.view',
                // No support.agents.manage — an agent manages their own
                // availability via their own SupportAgent record directly,
                // not through this permission (which governs seeing/toggling
                // OTHER agents).
            ],
        ];

        foreach ($platformRoles as $roleName => $permissions) {
            $role = Role::firstOrCreate([
                'name'       => $roleName,
                'guard_name' => 'platform',
                'tenant_id'  => null,
            ]);

            $role->syncPermissions(
                Permission::whereIn('name', $permissions)
                    ->where('guard_name', 'platform')
                    ->get()
            );
        }

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
            'vendors.assign', // assigning a vendor to an event — financial commitment (amount_agreed), separate from general edit

            // Budget
            'budget.view',
            'budget.manage',
            'budget.client-visibility.manage',

            // Guests
            'guests.view',        // full record, includes email/phone
            'guests.view.basic',  // name + RSVP status/count only, no PII
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
            'staff.roles.manage', // create/rename/delete roles, edit role permissions, set per-user overrides

            // Settings
            'settings.view',
            'settings.manage',

            // Client portal
            'client.portal.access',

            // Reports
            'reports.view',

            // Forms
            'forms.view',
            'forms.create',
            'forms.edit',
            'forms.delete',
            'forms.submissions.view',
            'forms.submissions.manage',

            // Vendor management
            'vendors.applications.view',
            'vendors.applications.manage', // approve/reject — owner-only by default

            // Client vendor involvement
            'vendors.client_involvement.manage', // toggle involvement level, approve/reject suggestions, finalize proposals

            // Client notification settings
            'client-notifications.manage',

            // Quick Access links (tenant-side management of staff/vendor links)
            'quick-access.manage',

            // Client account management (this new tenant-side Clients page)
            'clients.manage',

                        // Moodboards
            'moodboards.view',
            'moodboards.manage',

                        // Event Checklists
            'checklists.view',
            'checklists.manage',

            // Event Microsite (wedding/celebration guest-facing website)
            'microsite.view',
            'microsite.manage',

            // Vendor self (for vendor role)
            'vendor.profile.manage',
            'vendor.events.view',
            'vendor.runsheet.view',
            'vendor.payments.view',

            // Contracts
            'contracts.manage', // full lifecycle: create/edit/send/cancel/templates
            'contracts.view',   // view only, no edit

            // Invoices
            'invoices.manage', // full lifecycle: create/record payments/delete payments/cancel
            'invoices.view',   // view only, no edit

            // Billing (tenant's own subscription/plan)
            'billing.manage', // change plan, initiate checkout — owner-only by default
            'billing.view',

            // Domain settings
            'domain-settings.manage',

            // Assets
            'assets.manage', // full CRUD + assign to events

            // Conversations
            'conversations.create',
            'conversations.delete',              // delete entire conversation
            'conversations.manage_participants',  // add/remove people
        ];

        foreach ($tenantPermissions as $permission) {
            Permission::firstOrCreate([
                'name'       => $permission,
                'guard_name' => 'web',
            ]);
        }

        // We define ROLE TEMPLATES here (tenant_id = null)
        // When a tenant is created, TenantService copies these roles and
        // assigns them with that tenant's ID. company_owner always gets
        // the full $tenantPermissions array — every permission, including
        // any added here in the future — since it's the untouchable
        // is_system role.

        $roles = [
            'company_owner' => $tenantPermissions, // all permissions, always

            'coordinator'   => [
                'events.view', 'events.create', 'events.edit',
                'tasks.view', 'tasks.create', 'tasks.edit', 'tasks.assign',
                'vendors.view', 'vendors.create', 'vendors.edit', 'vendors.assign',
                'vendors.client_involvement.manage',
                'client-notifications.manage',
                'quick-access.manage',
                'clients.manage',
                'moodboards.view', 'moodboards.manage',
                'checklists.view', 'checklists.manage',
                'microsite.view', 'microsite.manage',
                'budget.view',
                'budget.client-visibility.manage',
                'guests.view', 'guests.create', 'guests.edit', 'guests.checkin',
                'rsvp.view', 'rsvp.manage',
                'runsheet.view', 'runsheet.manage',
                'documents.view', 'documents.upload',
                'reports.view',
                'forms.view', 'forms.create', 'forms.edit',
                'forms.submissions.view', 'forms.submissions.manage',
                'vendors.applications.view', // view only — approval is owner-only
                'contracts.manage',          // full contract ownership, per confirmed answer
                'staff.view',                // read-only staff visibility, no edit
                'conversations.create',
            ],

            'finance'       => [
                'events.view',   // view only, no write access
                'vendors.view',  // view only, no write access
                'vendors.assign', // financial commitment — finance territory alongside coordinator
                'budget.view', 'budget.manage',
                'reports.view',
                'contracts.view',  // view for reference only, no edit — contracts belong to coordinator
                'invoices.manage', // finance's real ownership: full invoice lifecycle
            ],

            'operations'    => [
                'events.view',
                'tasks.view', 'tasks.edit',
                'vendors.view', // view only — explicitly NOT vendors.assign (no financial commitment access)
                'runsheet.view', 'runsheet.manage',
                'guests.view', 'guests.checkin',
                'assets.manage', // day-of-event logistics ownership, per confirmed answer
                'moodboards.view', // reference-only — operations doesn't create creative direction, just reads it
                'checklists.view', 'checklists.manage', // operations owns day-of/logistics execution — this fits their existing runsheet/asset ownership better than view-only
            ],

            'social_media_manager' => [
                'events.view',
                'guests.view.basic', // names/counts only, never guests.view (no PII)
                'documents.view',
                // read-only across the board, no write permissions granted at all
            ],

            'client'        => [
                'client.portal.access',
                'events.view',
                'budget.view',
                'documents.view',
                'rsvp.view',
            ],

            'vendor' => [
                'vendor.profile.manage',
                'vendor.events.view',
                'vendor.runsheet.view',
                'vendor.payments.view',
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