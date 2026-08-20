<?php
namespace App\Events;

use App\Models\Tenant\RsvpResponse;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RsvpSubmitted
{
    use Dispatchable, SerializesModels;
    public function __construct(public RsvpResponse $response, public int $tenantId) {}
}