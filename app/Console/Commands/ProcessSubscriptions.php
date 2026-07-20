<?php

namespace App\Console\Commands;

use App\Jobs\SendSubscriptionExpiredJob;
use App\Jobs\SendSubscriptionReminderJob;
use App\Models\Central\BillingSetting;
use App\Models\Central\Subscription;
use App\Services\BillingService;
use Illuminate\Console\Command;

class ProcessSubscriptions extends Command
{
    protected $signature   = 'koordli:process-subscriptions';
    protected $description = 'Process expired subscriptions and send renewal reminders';

    public function handle(BillingService $billing): void
    {
        // 1. Process expired subscriptions
        $billing->processExpiredSubscriptions();
        $this->info('Processed expired subscriptions.');

        // 2. Send 14-day reminders
        $days14 = (int) BillingSetting::get('reminder_days', 14);
        $remind14 = Subscription::whereIn('status', ['active', 'trial'])
            ->where('reminder_14_sent', false)
            ->where(function ($q) use ($days14) {
                $q->whereDate('expires_at', now()->addDays($days14)->toDateString())
                  ->orWhereDate('trial_ends_at', now()->addDays($days14)->toDateString());
            })
            ->with(['tenant.users' => fn($q) => $q->withoutGlobalScope('tenant')->orderBy('id')])
            ->get();

        foreach ($remind14 as $sub) {
            $owner   = $sub->tenant->users->first();
            $expiry  = $sub->expires_at ?? $sub->trial_ends_at;
            if (!$owner || !$expiry) continue;

            SendSubscriptionReminderJob::dispatch(
                $owner->email,
                $sub->tenant->name,
                $sub->plan?->name ?? 'Your Plan',
                $expiry->format('D, d M Y'),
                $days14,
                route('tenant.billing.upgrade'),
            );

            $sub->update(['reminder_14_sent' => true]);
        }
        $this->info("Sent {$remind14->count()} 14-day reminders.");

        // 3. Send 3-day urgent reminders
        $days3 = (int) BillingSetting::get('reminder_days_urgent', 3);
        $remind3 = Subscription::whereIn('status', ['active', 'trial'])
            ->where('reminder_3_sent', false)
            ->where(function ($q) use ($days3) {
                $q->whereDate('expires_at', now()->addDays($days3)->toDateString())
                  ->orWhereDate('trial_ends_at', now()->addDays($days3)->toDateString());
            })
            ->with(['tenant.users' => fn($q) => $q->withoutGlobalScope('tenant')->orderBy('id')])
            ->get();

        foreach ($remind3 as $sub) {
            $owner  = $sub->tenant->users->first();
            $expiry = $sub->expires_at ?? $sub->trial_ends_at;
            if (!$owner || !$expiry) continue;

            SendSubscriptionReminderJob::dispatch(
                $owner->email,
                $sub->tenant->name,
                $sub->plan?->name ?? 'Your Plan',
                $expiry->format('D, d M Y'),
                $days3,
                route('tenant.billing.upgrade'),
            );

            $sub->update(['reminder_3_sent' => true]);
        }
        $this->info("Sent {$remind3->count()} 3-day urgent reminders.");

        // 4. Send expired notifications
        $justExpired = Subscription::where('status', 'expired')
            ->whereDate('expires_at', today())
            ->with(['tenant.users' => fn($q) => $q->withoutGlobalScope('tenant')->orderBy('id')])
            ->get();

        foreach ($justExpired as $sub) {
            $owner = $sub->tenant->users->first();
            if (!$owner) continue;

            SendSubscriptionExpiredJob::dispatch(
                $owner->email,
                $sub->tenant->name,
                $sub->plan?->name ?? 'Your Plan',
                route('tenant.billing.upgrade'),
            );
        }
        $this->info("Sent {$justExpired->count()} expiry notifications.");
    }
}