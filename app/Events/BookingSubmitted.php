<?php
namespace App\Events;

use App\Models\Tenant\FormSubmission;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BookingSubmitted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public FormSubmission $submission,
        public int $tenantId,
        public string $guestName,
        public string $formName,
    ) {}
}