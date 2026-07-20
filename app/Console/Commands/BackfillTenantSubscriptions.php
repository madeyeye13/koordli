<?php

namespace App\Console\Commands;

use App\Models\Central\BillingSetting;
use App\Models\Central\Subscription;
use App\Models\Central\Tenant;
use Illuminate\Console\Command;

class BackfillTenantSubscriptions extends Command
{
    protected $signature   = 'koordli:backfill-subscriptions';
    protected $description = 'Create subscription records for tenants that have none (pre-Phase 9 tenants)';

    public function handle(): void
    {
        $tenants = Tenant::whereDoesntHave('subscriptions')->get();

        if ($tenants->isEmpty()) {
            $this->info('All tenants already have subscription records.');
            return;
        }

        $this->info("Found {$tenants->count()} tenant(s) without subscription records.");
        $graceDays = (int) BillingSetting::get('grace_period_days', 7);

        foreach ($tenants as $tenant) {
            // Assume trial has already ended since these are pre-Phase 9 tenants
            $trialEnded = now()->subDays(1); // expired yesterday
            $graceUntil = now()->addDays($graceDays);

            Subscription::create([
                'tenant_id'             => $tenant->id,
                'plan_id'               => $tenant->plan_id,
                'status'                => 'expired',
                'trial_ends_at'         => $trialEnded,
                'current_period_start'  => $trialEnded->copy()->subDays(30),
                'current_period_end'    => $trialEnded,
                'expires_at'            => $trialEnded,
                'grace_until'           => $graceUntil,
                'billing_cycle'         => 'monthly',
                'currency'              => $tenant->billing_currency ?? 'NGN',
                'amount'                => 0,
                'reminder_14_sent'      => true, // don't send retroactive reminders
                'reminder_3_sent'       => true,
            ]);

            // Update tenant status
            $tenant->update(['status' => 'expired']);

            $this->info("✓ Created expired subscription for: {$tenant->name}");
        }

        $this->info('Done. Run php artisan koordli:process-subscriptions to process any further expiries.');
    }
}