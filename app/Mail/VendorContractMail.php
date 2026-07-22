<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VendorContractMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $vendorEmail,
        public readonly string $vendorName,
        public readonly string $contractTitle,
        public readonly string $companyName,
        public readonly string $pdfPath,
        public readonly string $viewUrl,
        public readonly bool   $whiteLabel = false,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Contract from {$this->companyName}: {$this->contractTitle}");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.vendor-contract');
    }

    public function attachments(): array
    {
        return [
            Attachment::fromStorageDisk('public', $this->pdfPath)
                ->as(str_replace(' ', '-', $this->contractTitle) . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}