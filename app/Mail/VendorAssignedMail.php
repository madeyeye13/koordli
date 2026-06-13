<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VendorAssignedMail extends Mailable
{
    use Queueable, SerializesModels;

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

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "You've been assigned to an event — {$this->companyName}",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.vendor-assigned');
    }
}