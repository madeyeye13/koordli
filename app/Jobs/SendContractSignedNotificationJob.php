<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendContractSignedNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 60;

    public function __construct(
        public readonly string $plannerEmail,
        public readonly string $plannerName,
        public readonly string $vendorName,
        public readonly string $contractTitle,
        public readonly string $signedAt,
        public readonly string $contractUrl,
        public readonly bool   $fullySigned,
    ) {}

    public function handle(): void
    {
        Mail::to($this->plannerEmail)->send(
            new \App\Mail\ContractSignedNotificationMail(
                $this->plannerEmail,
                $this->plannerName,
                $this->vendorName,
                $this->contractTitle,
                $this->signedAt,
                $this->contractUrl,
                $this->fullySigned,
            )
        );
    }
}