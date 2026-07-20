<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendSubscriptionExpiredJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries  = 3;
    public int $backoff = 60;

    public function __construct(
        public readonly string $tenantEmail,
        public readonly string $tenantName,
        public readonly string $planName,
        public readonly string $upgradeUrl,
    ) {}

    public function handle(): void
    {
        Mail::to($this->tenantEmail)->send(
            new \App\Mail\SubscriptionExpiredMail(
                $this->tenantEmail,
                $this->tenantName,
                $this->planName,
                $this->upgradeUrl,
            )
        );
    }
}