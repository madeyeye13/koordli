<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OutstandingReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $clientName,
        public string $clientEmail,
        public string $eventName,
        public string $companyName,
        public string $agreedBudget,
        public string $amountPaid,
        public string $outstanding,
        public string $currency,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Payment Reminder — {$this->eventName}",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.outstanding-reminder');
    }
}