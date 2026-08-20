<?php
namespace App\Events;

use App\Models\Tenant\RunsheetItem;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RunsheetItemDelayed
{
    use Dispatchable, SerializesModels;
    public function __construct(public RunsheetItem $item) {}
}