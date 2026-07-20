<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionActivatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $tenantEmail,
        public readonly string $tenantName,
        public readonly string $planName,
        public readonly string $billingCycle,
        public readonly string $amount,
        public readonly string $currency,
        public readonly string $expiresAt,
        public readonly string $gateway,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Your {$this->planName} plan is now active — Koordli");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.subscription-activated');
    }
}