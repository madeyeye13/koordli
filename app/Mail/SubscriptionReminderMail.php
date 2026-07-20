<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $tenantEmail,
        public readonly string $tenantName,
        public readonly string $planName,
        public readonly string $expiryDate,
        public readonly int    $daysLeft,
        public readonly string $upgradeUrl,
    ) {}

    public function envelope(): Envelope
    {
        $urgency = $this->daysLeft <= 3 ? '⚠️ Urgent: ' : '';
        return new Envelope(
            subject: "{$urgency}Your Koordli plan expires in {$this->daysLeft} " . ($this->daysLeft === 1 ? 'day' : 'days'),
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.subscription-reminder');
    }
}