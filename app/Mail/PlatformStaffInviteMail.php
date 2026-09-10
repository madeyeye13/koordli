<?php
namespace App\Mail;
use App\Models\Central\PlatformUser;
use Illuminate\Mail\Mailable;

class PlatformStaffInviteMail extends Mailable
{
    public function __construct(public PlatformUser $user, public string $invitedByName) {}
    public function build()
    {
        return $this->subject('You\'ve been invited to Koordli\'s platform team')
            ->view('emails.platform-staff-invite');
    }
}