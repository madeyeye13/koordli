<?php

namespace App\Console\Commands;

use App\Models\Central\Tenant;
use App\Models\Central\TenantNotificationSettings;
use App\Models\Tenant\Task;
use App\Models\Tenant\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendNotificationDigests extends Command
{
    protected $signature   = 'koordli:send-digests';
    protected $description = 'Sends daily/weekly digest emails per tenant notification settings';

    public function handle(): void
    {
        $isMondayMorning = now()->isMonday() && now()->hour === 7;
        $isDailyMorning  = now()->hour === 7;

        Tenant::chunk(50, function ($tenants) use ($isMondayMorning, $isDailyMorning) {
            foreach ($tenants as $tenant) {
                $settings = TenantNotificationSettings::forTenant($tenant->id);

                $shouldSend = ($settings->digest_mode === 'daily' && $isDailyMorning)
                    || ($settings->digest_mode === 'weekly' && $isMondayMorning);

                if (!$shouldSend) continue;

                $period = $settings->digest_mode === 'weekly' ? 'week' : 'day';
                $alreadySent = DB::table('notification_digest_log')
                    ->where('tenant_id', $tenant->id)
                    ->whereDate('sent_at', today())
                    ->exists();
                if ($alreadySent) continue;

                $users = User::withoutGlobalScope('tenant')
                    ->where('tenant_id', $tenant->id)
                    ->where('is_active', true)
                    ->get();

                foreach ($users as $user) {
                    $since = $period === 'week' ? now()->subWeek() : now()->subDay();

                    $tasksDueToday = Task::withoutGlobalScope('tenant')
                        ->where('tenant_id', $tenant->id)
                        ->where('assigned_to', $user->id)
                        ->pending()->whereDate('due_date', today())->get();

                    $overdueTasks = Task::withoutGlobalScope('tenant')
                        ->where('tenant_id', $tenant->id)
                        ->where('assigned_to', $user->id)
                        ->overdue()->get();

                    $unreadNotifications = $user->notifications()
                        ->whereNull('read_at')
                        ->where('created_at', '>=', $since)
                        ->get();

                    if ($tasksDueToday->isEmpty() && $overdueTasks->isEmpty() && $unreadNotifications->isEmpty()) {
                        continue; // nothing to report — don't send an empty digest
                    }

                    Mail::to($user->email)->queue(new \App\Mail\NotificationDigestMail(
                        $user->name,
                        $period,
                        $tasksDueToday,
                        $overdueTasks,
                        $unreadNotifications,
                    ));
                }

                DB::table('notification_digest_log')->insert([
                    'tenant_id'  => $tenant->id,
                    'sent_at'    => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $this->info("Digest sent for tenant: {$tenant->name}");
            }
        });
    }
}