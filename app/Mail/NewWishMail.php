<?php
// app/Mail/NewWishMail.php
namespace App\Mail;

use App\Models\Tenant\EventWish;
use Illuminate\Mail\Mailable;

class NewWishMail extends Mailable
{
    public function __construct(public EventWish $wish, public string $recipientName) {}

    public function build()
    {
        return $this->subject('New wish for ' . ($this->wish->event->name ?? 'your event'))
            ->view('emails.new-wish');
    }
}