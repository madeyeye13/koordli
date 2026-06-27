<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FormSubmissionNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string  $recipientEmail,
        public readonly string  $recipientName,
        public readonly string  $formName,
        public readonly string  $formType,
        public readonly string  $submitterName,
        public readonly string  $submittedAt,
        public readonly array   $fields = [],
        public readonly ?string $bookingDate = null,
        public readonly ?string $bookingTime = null,
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->formType === 'consultation'
            ? "New Consultation Booking — {$this->formName}"
            : "New Booking Enquiry — {$this->formName}";

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.form-submission-notification');
    }
}