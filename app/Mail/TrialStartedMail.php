<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TrialStartedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $tenantEmail,
        public readonly string $tenantName,
        public readonly string $companyName,
        public readonly string $planName,
        public readonly int    $trialDays,
        public readonly string $trialEndsAt,
        public readonly string $dashboardUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your {$this->trialDays}-day free trial has started",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.trial-started',
            with: [
                'name'         => $this->tenantName,
                'companyName'  => $this->companyName,
                'planName'     => $this->planName,
                'trialDays'    => $this->trialDays,
                'trialEndsAt'  => $this->trialEndsAt,
                'dashboardUrl' => $this->dashboardUrl,
            ],
        );
    }
}