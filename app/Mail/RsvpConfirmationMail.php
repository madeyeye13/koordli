<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RsvpConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $guestEmail,
        public readonly string $guestName,
        public readonly string $eventName,
        public readonly string $eventDate,
        public readonly string $venue,
        public readonly string $status,
        public readonly string $qrToken,
        public readonly string $editUrl,
        public readonly int    $plusOneCount,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->status === 'confirmed'
                ? "✓ RSVP Confirmed — {$this->eventName}"
                : "RSVP Updated — {$this->eventName}",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.rsvp-confirmation');
    }
}