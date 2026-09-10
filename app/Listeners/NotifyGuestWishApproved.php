<?php
// app/Listeners/NotifyGuestWishApproved.php
namespace App\Listeners;

use App\Events\WishApproved;
use App\Mail\WishApprovedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class NotifyGuestWishApproved implements ShouldQueue
{
    public function handle(WishApproved $event): void
    {
        if ($event->wish->guest_email) {
            Mail::to($event->wish->guest_email)->send(new WishApprovedMail($event->wish));
        }
    }
}