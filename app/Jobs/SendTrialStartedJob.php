<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendTrialStartedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 60;

    public function __construct(
        public readonly string $tenantEmail,
        public readonly string $tenantName,
        public readonly string $companyName,
        public readonly string $planName,
        public readonly int    $trialDays,
        public readonly string $trialEndsAt,
        public readonly string $dashboardUrl,
    ) {}

    public function handle(): void
    {
        Mail::to($this->tenantEmail)->send(
            new \App\Mail\TrialStartedMail(
                $this->tenantEmail,
                $this->tenantName,
                $this->companyName,
                $this->planName,
                $this->trialDays,
                $this->trialEndsAt,
                $this->dashboardUrl,
            )
        );
    }
}