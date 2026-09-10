<?php
namespace App\Mail;
use Illuminate\Mail\Mailable;
class PlatformEmailChangeCodeMail extends Mailable {
    public function __construct(public string $name, public string $code) {}
    public function build() { return $this->subject('Verify your new Koordli email')->view('emails.platform-email-change-code'); }
}