<?php
namespace App\Mail;
use App\Models\Central\PlatformUser;
use Illuminate\Mail\Mailable;

class PlatformRoleChangedMail extends Mailable
{
    public function __construct(public PlatformUser $user, public string $newRole) {}
    public function build()
    {
        return $this->subject('Your Koordli platform role has changed')
            ->view('emails.platform-role-changed');
    }
}