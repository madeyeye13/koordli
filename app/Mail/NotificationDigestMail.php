<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificationDigestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $userName,
        public string $period, // day|week
        public $tasksDueToday,
        public $overdueTasks,
        public $unreadNotifications,
    ) {}

    public function envelope(): Envelope
    {
        $label = $this->period === 'week' ? 'Weekly' : 'Daily';
        return new Envelope(subject: "Your {$label} Koordli Summary");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.notification-digest');
    }
}