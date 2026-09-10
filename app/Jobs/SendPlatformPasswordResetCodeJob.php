<?php

namespace App\Jobs;

use App\Mail\PlatformPasswordResetCodeMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendPlatformPasswordResetCodeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Real automatic retry, not just a single attempt — a transient
    // mail-server hiccup shouldn't leave someone stuck without their code.
    public int $tries = 3;
    public array $backoff = [30, 120, 300]; // 30s, then 2min, then 5min

    public function __construct(
        public string $email,
        public string $name,
        public string $code,
    ) {}

    public function handle(): void
    {
        Mail::to($this->email)->send(new PlatformPasswordResetCodeMail($this->name, $this->code));
    }
}