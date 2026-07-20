<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanPrice extends Model
{
    protected $fillable = [
        'plan_id', 'currency', 'amount', 'billing_cycle',
        'amount_with_charges', 'annual_discount_percent',
        'gateway', 'gateway_plan_id', 'is_active',
    ];

    protected $casts = [
        'amount'                 => 'decimal:2',
        'amount_with_charges'    => 'decimal:2',
        'annual_discount_percent'=> 'decimal:2',
        'is_active'              => 'boolean',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function displayAmount(): float
    {
        return (float) ($this->amount_with_charges ?? $this->amount);
    }

    public function annualSavings(): float
    {
        if (!$this->annual_discount_percent) return 0;
        $monthly = $this->amount * 12;
        return $monthly - ($this->amount * 12 * (1 - $this->annual_discount_percent / 100));
    }
}