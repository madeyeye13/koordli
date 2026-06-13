<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendVendorAssignedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries  = 3;
    public int $backoff = 60;

    public function __construct(
        public readonly string $vendorEmail,
        public readonly string $vendorName,
        public readonly string $businessName,
        public readonly string $eventName,
        public readonly string $eventDate,
        public readonly string $companyName,
        public readonly bool   $isNewAccount,
        public readonly string $password = '',
    ) {}

    public function handle(): void
    {
        Mail::to($this->vendorEmail)->send(
            new \App\Mail\VendorAssignedMail(
                $this->vendorEmail,
                $this->vendorName,
                $this->businessName,
                $this->eventName,
                $this->eventDate,
                $this->companyName,
                $this->isNewAccount,
                $this->password,
            )
        );
    }
}