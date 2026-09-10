<?php
namespace App\Mail;
use Illuminate\Mail\Mailable;

class PlatformPasswordChangedMail extends Mailable
{
    public function __construct(public string $name) {}
    public function build()
    {
        return $this->subject('Your Koordli platform password was changed')
            ->view('emails.platform-password-changed');
    }
}