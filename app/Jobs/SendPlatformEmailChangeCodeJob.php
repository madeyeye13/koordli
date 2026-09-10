<?php
// app/Jobs/SendPlatformEmailChangeCodeJob.php
namespace App\Jobs;
use App\Mail\PlatformEmailChangeCodeMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendPlatformEmailChangeCodeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public int $tries = 3;
    public array $backoff = [30, 120, 300];

    public function __construct(public string $newEmail, public string $name, public string $code) {}

    public function handle(): void
    {
        Mail::to($this->newEmail)->send(new PlatformEmailChangeCodeMail($this->name, $this->code));
    }
}