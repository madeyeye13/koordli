<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VendorApplicationReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $vendorEmail,
        public readonly string $vendorName,
        public readonly string $businessName,
        public readonly string $companyName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Application received — {$this->companyName} vendor portal");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.vendor-application-received');
    }
}