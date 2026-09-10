<?php

namespace Tests\Feature;

use App\Models\Central\Plan;
use App\Services\TenantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TenantSubscriptionModeTest extends TestCase
{
    use RefreshDatabase;

    public function test_platform_can_create_trial_subscription_for_plan(): void
    {
        $plan = Plan::create([
            'name' => 'Starter Trial',
            'slug' => 'starter-trial',
            'billing_cycle' => 'monthly',
            'trial_days' => 14,
            'is_active' => true,
            'is_featured' => false,
            'is_contact_only' => false,
            'annual_discount_percent' => 0,
            'features' => [],
            'limits' => [],
            'allowed_cycles' => ['monthly', 'annual'],
        ]);

        $service = app(TenantService::class);
        $tenant = $service->create([
            'name' => 'Trial Co',
            'owner_name' => 'Alicia',
            'owner_email' => 'alicia@example.com',
            'owner_password' => 'Password1',
            'billing_currency' => 'NGN',
            'country' => 'NG',
            'plan_id' => $plan->id,
            'subscription_mode' => 'trial',
            'subscription_cycle' => 'monthly',
            'is_self_registered' => false,
        ]);

        $subscription = DB::table('subscriptions')->where('tenant_id', $tenant->id)->first();

        $this->assertNotNull($subscription);
        $this->assertSame('trial', $subscription->status);
        $this->assertSame('monthly', $subscription->billing_cycle);
        $this->assertNotNull($subscription->trial_ends_at);
    }

    public function test_platform_can_create_direct_annual_subscription_for_plan(): void
    {
        $plan = Plan::create([
            'name' => 'Growth Annual',
            'slug' => 'growth-annual',
            'billing_cycle' => 'monthly',
            'trial_days' => 0,
            'is_active' => true,
            'is_featured' => false,
            'is_contact_only' => false,
            'annual_discount_percent' => 10,
            'features' => [],
            'limits' => [],
            'allowed_cycles' => ['monthly', 'annual'],
        ]);

        $service = app(TenantService::class);
        $tenant = $service->create([
            'name' => 'Annual Co',
            'owner_name' => 'Ben',
            'owner_email' => 'ben@example.com',
            'owner_password' => 'Password1',
            'billing_currency' => 'NGN',
            'country' => 'NG',
            'plan_id' => $plan->id,
            'subscription_mode' => 'direct',
            'subscription_cycle' => 'annual',
            'is_self_registered' => false,
        ]);

        $subscription = DB::table('subscriptions')->where('tenant_id', $tenant->id)->first();

        $this->assertNotNull($subscription);
        $this->assertSame('active', $subscription->status);
        $this->assertSame('annual', $subscription->billing_cycle);
        $this->assertNotNull($subscription->expires_at);
        $this->assertNull($subscription->trial_ends_at);
    }
}
