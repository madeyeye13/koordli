<?php

namespace App\Jobs;

use App\Mail\FeedbackSubmissionReceivedMail;
use App\Models\Central\FeedbackSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendFeedbackSubmissionNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(public readonly FeedbackSubmission $feedback) {}

    public function handle(): void
    {
        $recipient = config('mail.feedback_recipient', config('mail.from.address'));

        if (!$recipient) {
            return;
        }

        Mail::to($recipient)->send(new FeedbackSubmissionReceivedMail($this->feedback));
    }
}
