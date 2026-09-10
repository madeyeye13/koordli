<?php
namespace App\Mail;
use Illuminate\Mail\Mailable;

class PlatformPasswordResetCodeMail extends Mailable
{
    public function __construct(public string $name, public string $code) {}
    public function build()
    {
        return $this->subject('Your Koordli platform verification code')
            ->view('emails.platform-password-reset-code');
    }
}