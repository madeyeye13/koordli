<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FormSubmissionConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

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

    public function envelope(): Envelope
    {
        $subject = $this->formType === 'consultation'
            ? "Consultation Booking Confirmed — {$this->formName}"
            : "Booking Received — {$this->formName}";

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.form-submission-confirmation');
    }
}