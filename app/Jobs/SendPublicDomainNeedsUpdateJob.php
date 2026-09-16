<?php

namespace App\Jobs;

use App\Mail\PublicDomainNeedsUpdateMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendPublicDomainNeedsUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public readonly string $recipientEmail,
        public readonly string $recipientName,
        public readonly string $eventName,
        public readonly string $domain,
        public readonly string $domainType,
        public readonly string $failureMessage,
        public readonly string $expectedDnsValue,
        public readonly ?string $observedDnsValue,
        public readonly string $managerUrl,
    ) {}

    public function handle(): void
    {
        Mail::to($this->recipientEmail)->send(new PublicDomainNeedsUpdateMail(
            $this->recipientEmail,
            $this->recipientName,
            $this->eventName,
            $this->domain,
            $this->domainType,
            $this->failureMessage,
            $this->expectedDnsValue,
            $this->observedDnsValue,
            $this->managerUrl,
        ));
    }
}
