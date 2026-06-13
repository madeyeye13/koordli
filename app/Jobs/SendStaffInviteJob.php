<?php

namespace App\Jobs;

use App\Mail\StaffInviteMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendStaffInviteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $staffEmail,
        public string $staffName,
        public string $tempPassword,
        public string $companyName,
        public string $inviterName,
    ) {}

    public function handle(): void
    {
        Mail::to($this->staffEmail)->send(new StaffInviteMail(
            staffName:    $this->staffName,
            staffEmail:   $this->staffEmail,
            tempPassword: $this->tempPassword,
            companyName:  $this->companyName,
            inviterName:  $this->inviterName,
        ));
    }
}