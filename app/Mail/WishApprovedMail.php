<?php
// app/Mail/WishApprovedMail.php
namespace App\Mail;

use App\Models\Tenant\EventWish;
use Illuminate\Mail\Mailable;

class WishApprovedMail extends Mailable
{
    public function __construct(public EventWish $wish) {}

    public function build()
    {
        return $this->subject('Your wish is now live!')
            ->view('emails.wish-approved');
    }
}