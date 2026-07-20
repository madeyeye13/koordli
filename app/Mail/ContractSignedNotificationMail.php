<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContractSignedNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $plannerEmail,
        public readonly string $plannerName,
        public readonly string $vendorName,
        public readonly string $contractTitle,
        public readonly string $signedAt,
        public readonly string $contractUrl,
        public readonly bool   $fullySigned,
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->fullySigned
            ? "✅ Contract fully signed — {$this->contractTitle}"
            : "✍️ {$this->vendorName} signed your contract";

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.contract-signed-notification');
    }
}