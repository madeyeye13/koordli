<?php
// app/Jobs/SendPlatformEmailChangeAlertJob.php
namespace App\Jobs;
use App\Mail\PlatformEmailChangeAlertMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendPlatformEmailChangeAlertJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public int $tries = 3;
    public array $backoff = [30, 120, 300];

    public function __construct(public string $oldEmail, public string $name, public string $attemptedNewEmail) {}

    public function handle(): void
    {
        Mail::to($this->oldEmail)->send(new PlatformEmailChangeAlertMail($this->name, $this->attemptedNewEmail));
    }
}