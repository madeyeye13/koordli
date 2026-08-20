<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConversationAddedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $recipientName,
        public string $conversationName,
        public string $eventName,
        public string $addedByName,
        public string $portalUrl,
        public string $companyName,
        public bool $whiteLabel,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "You've been added to a conversation — {$this->eventName}");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.conversation-added');
    }
}