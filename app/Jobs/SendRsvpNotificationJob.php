<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendRsvpNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries  = 3;
    public int $backoff = 60;

    public function __construct(
        public readonly string  $recipientEmail,
        public readonly string  $recipientName,
        public readonly string  $guestName,
        public readonly string  $eventName,
        public readonly string  $status,
        public readonly int     $plusOneCount,
        public readonly bool    $isUpdate = false,
    ) {}

    public function handle(): void
    {
        Mail::to($this->recipientEmail)->send(
            new \App\Mail\RsvpNotificationMail(
                $this->recipientEmail,
                $this->recipientName,
                $this->guestName,
                $this->eventName,
                $this->status,
                $this->plusOneCount,
                $this->isUpdate,
            )
        );
    }
}