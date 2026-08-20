<?php
namespace App\Events;

use App\Models\Tenant\VendorContract;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContractFullySigned
{
    use Dispatchable, SerializesModels;
    public function __construct(public VendorContract $contract) {}
}