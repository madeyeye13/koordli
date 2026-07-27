<?php

namespace App\Services;

use App\Models\Central\IndustryProfile;
use App\Models\Central\Tenant;
use Database\Seeders\DefaultTenantSeeder;
use Illuminate\Support\Facades\DB;

/**
 * Single centralized entry point for ALL tenant provisioning, regardless of
 * how the tenant was created (self-registration, platform-owner manual
 * creation, or any future API/import path). Every caller MUST go through
 * this service rather than calling TenantService::create() + seeding
 * directly, so provisioning behavior never drifts between entry points.
 */
class TenantProvisioningService
{
    public function __construct(
        protected TenantService $tenantService,
    ) {}

    public function provision(array $data): Tenant
    {
        return DB::transaction(function () use ($data) {
            $profile = !empty($data['industry_profile_id'])
                ? IndustryProfile::find($data['industry_profile_id'])
                : null;

            // 1. Create the tenant + owner user (existing, proven logic — untouched)
            $tenant = $this->tenantService->create([
                ...$data,
                'industry_profile_id' => $profile?->id,
            ]);

            // 2. Seed default event types / vendor categories / task categories / statuses / labels
            (new DefaultTenantSeeder())->run($tenant->id, $profile);

            // 3. Enable recommended feature flags for this profile (tenant-level override,
            //    layered the same way tenant_feature_overrides already works elsewhere)
            if ($profile && !empty($profile->recommended_feature_flags)) {
                $this->enableRecommendedFeatures($tenant, $profile->recommended_feature_flags);
            }

            // 4. Seed default roles (Spatie teams-scoped) — only if the profile specifies any;
            //    falls back to whatever TenantService::create() already seeds today otherwise
            if ($profile && !empty($profile->default_roles)) {
                $this->seedProfileRoles($tenant, $profile->default_roles);
            }

            return $tenant;
        });
    }

    protected function enableRecommendedFeatures(Tenant $tenant, array $featureKeys): void
    {
        $flags = \App\Models\Central\FeatureFlag::whereIn('key', $featureKeys)->get();

        foreach ($flags as $flag) {
            \App\Models\Central\TenantFeatureOverride::updateOrCreate(
                ['tenant_id' => $tenant->id, 'feature_flag_id' => $flag->id],
                ['value' => '1', 'expires_at' => null]
            );
        }
    }

    protected function seedProfileRoles(Tenant $tenant, array $roleNames): void
    {
        foreach ($roleNames as $roleName) {
            \Spatie\Permission\Models\Role::firstOrCreate([
                'name'       => $roleName,
                'guard_name' => 'web',
                'tenant_id'  => $tenant->id,
            ]);
        }
    }
}