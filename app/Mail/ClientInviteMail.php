<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClientInviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $clientEmail,
        public readonly string $clientName,
        public readonly string $password,
        public readonly string $companyName,
        public readonly string $eventName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "You've been invited to view your event on Koordli",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.client-invite',
        );
    }
}