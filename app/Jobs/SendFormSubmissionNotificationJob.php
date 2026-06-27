<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendFormSubmissionNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries  = 3;
    public int $backoff = 60;

    public function __construct(
        public readonly string $recipientEmail,
        public readonly string $recipientName,
        public readonly string $formName,
        public readonly string $formType,
        public readonly string $submitterName,
        public readonly string $submittedAt,
        public readonly array  $fields = [],
        public readonly ?string $bookingDate = null,
        public readonly ?string $bookingTime = null,
    ) {}

    public function handle(): void
    {
        Mail::to($this->recipientEmail)->send(
            new \App\Mail\FormSubmissionNotificationMail(
                $this->recipientEmail,
                $this->recipientName,
                $this->formName,
                $this->formType,
                $this->submitterName,
                $this->submittedAt,
                $this->fields,
                $this->bookingDate,
                $this->bookingTime,
            )
        );
    }
}