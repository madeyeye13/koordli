<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StaffInviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $staffName,
        public string $staffEmail,
        public string $tempPassword,
        public string $companyName,
        public string $inviterName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "You've been invited to join {$this->companyName} on Koordli",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.staff-invite',
        );
    }
}