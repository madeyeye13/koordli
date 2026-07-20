<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendSubscriptionActivatedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 60;

    public function __construct(
        public readonly string $tenantEmail,
        public readonly string $tenantName,
        public readonly string $planName,
        public readonly string $billingCycle,
        public readonly string $amount,
        public readonly string $currency,
        public readonly string $expiresAt,
        public readonly string $gateway,
    ) {}

    public function handle(): void
    {
        Mail::to($this->tenantEmail)->send(
            new \App\Mail\SubscriptionActivatedMail(
                $this->tenantEmail,
                $this->tenantName,
                $this->planName,
                $this->billingCycle,
                $this->amount,
                $this->currency,
                $this->expiresAt,
                $this->gateway,
            )
        );
    }
}