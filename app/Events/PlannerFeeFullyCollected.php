<?php

namespace App\Events;

use App\Models\Tenant\Budget;
use Illuminate\Foundation\Events\Dispatchable;

class PlannerFeeFullyCollected
{
    use Dispatchable;

    public function __construct(public Budget $budget) {}
}