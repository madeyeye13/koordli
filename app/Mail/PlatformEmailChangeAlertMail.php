<?php
namespace App\Mail;
use Illuminate\Mail\Mailable;
class PlatformEmailChangeAlertMail extends Mailable {
    public function __construct(public string $name, public string $attemptedNewEmail) {}
    public function build() { return $this->subject('Your Koordli email change was requested')->view('emails.platform-email-change-alert'); }
}