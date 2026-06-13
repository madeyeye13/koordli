<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendClientInviteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public readonly string $clientEmail,
        public readonly string $clientName,
        public readonly string $password,
        public readonly string $companyName,
        public readonly string $eventName,
    ) {}

    public function handle(): void
    {
        Mail::to($this->clientEmail)->send(
            new \App\Mail\ClientInviteMail(
                $this->clientEmail,
                $this->clientName,
                $this->password,
                $this->companyName,
                $this->eventName,
            )
        );
    }
}