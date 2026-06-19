<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RsvpNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $recipientEmail,
        public readonly string $recipientName,
        public readonly string $guestName,
        public readonly string $eventName,
        public readonly string $status,
        public readonly int    $plusOneCount,
        public readonly bool   $isUpdate = false,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->isUpdate
                ? "RSVP Updated — {$this->eventName}"
                : "New RSVP — {$this->eventName}",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.rsvp-notification');
    }
}