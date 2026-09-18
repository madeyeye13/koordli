<?php

namespace App\Mail;

use App\Models\Central\FeedbackSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FeedbackSubmissionReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly FeedbackSubmission $feedback) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "New Koordli tester feedback from {$this->feedback->name}");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.feedback-submission-received');
    }
}
