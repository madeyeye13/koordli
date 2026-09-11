<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TenantSuspendedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $tenantEmail,
        public readonly string $tenantName,
        public readonly string $supportUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your Koordli company account has been suspended');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.tenant-suspended');
    }
}
