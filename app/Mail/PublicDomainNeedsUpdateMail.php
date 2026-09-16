<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PublicDomainNeedsUpdateMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $recipientEmail,
        public readonly string $recipientName,
        public readonly string $eventName,
        public readonly string $domain,
        public readonly string $domainType,
        public readonly string $failureMessage,
        public readonly string $expectedDnsValue,
        public readonly ?string $observedDnsValue,
        public readonly string $managerUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Action needed: RSVP domain {$this->domain}");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.public-domain-needs-update');
    }
}
