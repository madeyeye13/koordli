<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendSubscriptionReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries  = 3;
    public int $backoff = 60;

    public function __construct(
        public readonly string $tenantEmail,
        public readonly string $tenantName,
        public readonly string $planName,
        public readonly string $expiryDate,
        public readonly int    $daysLeft,
        public readonly string $upgradeUrl,
    ) {}

    public function handle(): void
    {
        Mail::to($this->tenantEmail)->send(
            new \App\Mail\SubscriptionReminderMail(
                $this->tenantEmail,
                $this->tenantName,
                $this->planName,
                $this->expiryDate,
                $this->daysLeft,
                $this->upgradeUrl,
            )
        );
    }
}