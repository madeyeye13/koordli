<?php

namespace App\Jobs;

use App\Mail\TenantSuspendedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendTenantSuspendedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public readonly string $tenantEmail,
        public readonly string $tenantName,
        public readonly string $supportUrl,
    ) {}

    public function handle(): void
    {
        Mail::to($this->tenantEmail)->send(
            new TenantSuspendedMail($this->tenantEmail, $this->tenantName, $this->supportUrl)
        );
    }
}
