<?php
namespace App\Events;

use App\Models\Tenant\VendorApplication;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VendorApplicationSubmitted
{
    use Dispatchable, SerializesModels;
    public function __construct(public VendorApplication $application) {}
}