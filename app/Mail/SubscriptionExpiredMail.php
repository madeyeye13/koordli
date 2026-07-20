<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionExpiredMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $tenantEmail,
        public readonly string $tenantName,
        public readonly string $planName,
        public readonly string $upgradeUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your Koordli subscription has expired');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.subscription-expired');
    }
}