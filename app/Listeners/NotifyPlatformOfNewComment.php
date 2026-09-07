<?php
// app/Listeners/NotifyPlatformOfNewComment.php
namespace App\Listeners;

use App\Events\BlogCommentSubmitted;
use App\Mail\NewBlogCommentMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class NotifyPlatformOfNewComment implements ShouldQueue
{
    public function handle(BlogCommentSubmitted $event): void
    {
        // Sends to the platform's own admin address — adjust to your
        // real notification email or an existing PlatformUser query.
        Mail::to(config('mail.admin_address', 'admin@koordli.com'))
            ->send(new NewBlogCommentMail($event->comment));
    }
}