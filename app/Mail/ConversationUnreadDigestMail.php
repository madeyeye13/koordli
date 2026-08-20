<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConversationUnreadDigestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $recipientName,
        public string $conversationName,
        public string $eventName,
        public int $unreadCount,
        public string $portalUrl,
        public string $companyName,
        public bool $whiteLabel,
    ) {}

    public function envelope(): Envelope
    {
        $sender = $this->whiteLabel ? $this->companyName : 'Koordli';
        return new Envelope(subject: "You have unread messages — {$this->eventName}", from: null);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.conversation-unread-digest');
    }
}