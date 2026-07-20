<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendPlatformPaymentNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 60;

    public function __construct(
        public readonly string $companyName,
        public readonly string $planName,
        public readonly string $billingCycle,
        public readonly string $amount,
        public readonly string $currency,
        public readonly string $amountNgn,
        public readonly string $gateway,
        public readonly string $paidAt,
    ) {}

    public function handle(): void
    {
        $platformEmail = config('mail.from.address');

        Mail::to($platformEmail)->send(
            new \App\Mail\PlatformPaymentNotificationMail(
                $this->companyName,
                $this->planName,
                $this->billingCycle,
                $this->amount,
                $this->currency,
                $this->amountNgn,
                $this->gateway,
                $this->paidAt,
            )
        );
    }
}