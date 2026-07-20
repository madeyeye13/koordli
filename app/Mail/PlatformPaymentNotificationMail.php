<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PlatformPaymentNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $companyName,
        public readonly string $planName,
        public readonly string $billingCycle,
        public readonly string $amount,
        public readonly string $currency,
        public readonly string $amountNgn,
        public readonly string $gateway,
        public readonly string $paidAt,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "💰 New payment: {$this->companyName} — {$this->currency} {$this->amount}");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.platform-payment-notification');
    }
}