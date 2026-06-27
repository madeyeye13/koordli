<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendFormSubmissionConfirmationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries  = 3;
    public int $backoff = 60;

    public function __construct(
        public readonly string  $guestEmail,
        public readonly string  $guestName,
        public readonly string  $formName,
        public readonly string  $formType,
        public readonly string  $tenantName,
        public readonly string  $tenantEmail,
        public readonly ?string $tenantPhone,
        public readonly ?string $bookingDate = null,
        public readonly ?string $bookingTime = null,
        public readonly ?string $consultationType = null,
        public readonly ?string $location = null,
        public readonly ?string $meetingLink = null,
    ) {}

    public function handle(): void
    {
        Mail::to($this->guestEmail)->send(
            new \App\Mail\FormSubmissionConfirmationMail(
                $this->guestEmail,
                $this->guestName,
                $this->formName,
                $this->formType,
                $this->tenantName,
                $this->tenantEmail,
                $this->tenantPhone,
                $this->bookingDate,
                $this->bookingTime,
                $this->consultationType,
                $this->location,
                $this->meetingLink,
            )
        );
    }
}