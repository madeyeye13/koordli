<?php

namespace App\Services;

use App\Models\Central\Plan;
use App\Models\Central\Tenant;
use App\Models\Tenant\User;
use App\Enums\UserType;
use Database\Seeders\DefaultTenantSeeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class TenantService
{
    public function create(array $data): Tenant
    {
        return DB::transaction(function () use ($data) {

            // 1. Get default plan
            $plan = Plan::first();

            // 2. Create tenant
            $tenant = Tenant::create([
                'uuid'             => Str::uuid(),
                'name'             => $data['name'],
                'slug'             => $this->generateSlug($data['name']),
                'status'           => 'trial',
                'plan_id'          => $data['plan_id'] ?? null,
                'billing_currency' => $data['billing_currency'] ?? 'NGN',
                'country'          => $data['country'] ?? null,
                'branding'         => [
                    'primary_color' => '#7C3AED',
                    'accent_color'  => '#F59E0B',
                ],
            ]);
            // Create subscription if plan provided
            if (!empty($data['plan_id'])) {
                $plan = Plan::find($data['plan_id']);
                if ($plan) {
                    DB::table('subscriptions')->insert([
                        'tenant_id'            => $tenant->id,
                        'plan_id'              => $plan->id,
                        'status'               => 'trial',
                        'trial_ends_at'        => now()->addDays($plan->trial_days ?? 30),
                        'current_period_start' => now(),
                        'current_period_end'   => now()->addDays($plan->trial_days ?? 30),
                        'currency'             => $data['billing_currency'] ?? 'NGN',
                        'amount'               => 0,
                        'created_at'           => now(),
                        'updated_at'           => now(),
                    ]);
                }
            }
            // 3. Create company owner user
            $user = User::create([
                'uuid'      => Str::uuid(),
                'tenant_id' => $tenant->id,
                'name'      => $data['owner_name'],
                'email'     => $data['owner_email'],
                'password'  => Hash::make($data['owner_password']),
                'type'      => UserType::Staff,
                'is_active' => true,
                'is_self_registered' => $data['is_self_registered'] ?? false,
            ]);

            // 4. Assign company_owner role scoped to this tenant
            app(\Spatie\Permission\PermissionRegistrar::class)
                ->setPermissionsTeamId($tenant->id);

            $tenantRole = Role::firstOrCreate([
                'name'       => 'company_owner',
                'guard_name' => 'web',
                'tenant_id'  => $tenant->id,
            ]);

            // Copy permissions from template role
            $templateRole = Role::where('name', 'company_owner')
                ->where('guard_name', 'web')
                ->whereNull('tenant_id')
                ->first();

            if ($templateRole) {
                $tenantRole->syncPermissions($templateRole->permissions);
            }

            $user->assignRole($tenantRole);

            // 5. Seed default tenant data
            $seeder = new DefaultTenantSeeder();
            $seeder->run($tenant->id);

            // 6. Send welcome email if manually created
            if (!($data['is_self_registered'] ?? false)) {
                \App\Jobs\SendWelcomeEmailJob::dispatch(
                    $data['owner_email'],
                    $data['owner_name'],
                    $data['owner_password'],
                    $tenant->name
                );
            }

            return $tenant;
        });
    }

    public function generateSlug(string $name): string
    {
        $slug = Str::slug($name);
        $count = Tenant::where('slug', 'like', $slug . '%')->count();
        return $count > 0 ? $slug . '-' . ($count + 1) : $slug;
    }

    public function suspend(Tenant $tenant): void
    {
        $tenant->update(['status' => 'suspended']);
    }

    public function activate(Tenant $tenant): void
    {
        $tenant->update(['status' => 'active']);
    }
}