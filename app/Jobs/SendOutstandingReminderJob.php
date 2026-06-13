<?php

namespace App\Jobs;

use App\Mail\OutstandingReminderMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendOutstandingReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $clientEmail,
        public string $clientName,
        public string $eventName,
        public string $companyName,
        public string $agreedBudget,
        public string $amountPaid,
        public string $outstanding,
        public string $currency,
    ) {}

    public function handle(): void
    {
        Mail::to($this->clientEmail)->send(new OutstandingReminderMail(
            clientName:   $this->clientName,
            clientEmail:  $this->clientEmail,
            eventName:    $this->eventName,
            companyName:  $this->companyName,
            agreedBudget: $this->agreedBudget,
            amountPaid:   $this->amountPaid,
            outstanding:  $this->outstanding,
            currency:     $this->currency,
        ));
    }
}