<?php

namespace App\Jobs;

use App\Mail\PlatformPasswordChangedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendPlatformPasswordChangedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [30, 120, 300];

    public function __construct(
        public string $email,
        public string $name,
    ) {}

    public function handle(): void
    {
        Mail::to($this->email)->send(new PlatformPasswordChangedMail($this->name));
    }
}