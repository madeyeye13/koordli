<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SupportTicketReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $tenantEmail,
        public readonly string $tenantName,
        public readonly string $ticketSubject,
        public readonly string $replyMessage,
        public readonly string $ticketUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Re: {$this->ticketSubject}");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.support-ticket-reply');
    }
}