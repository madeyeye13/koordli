<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendRsvpConfirmationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries  = 3;
    public int $backoff = 60;

    public function __construct(
        public readonly string $guestEmail,
        public readonly string $guestName,
        public readonly string $eventName,
        public readonly string $eventDate,
        public readonly string $venue,
        public readonly string $status,
        public readonly string $qrToken,
        public readonly string $editUrl,
        public readonly int    $plusOneCount,
    ) {}

    public function handle(): void
    {
        Mail::to($this->guestEmail)->send(
            new \App\Mail\RsvpConfirmationMail(
                $this->guestEmail,
                $this->guestName,
                $this->eventName,
                $this->eventDate,
                $this->venue,
                $this->status,
                $this->qrToken,
                $this->editUrl,
                $this->plusOneCount,
            )
        );
    }
}