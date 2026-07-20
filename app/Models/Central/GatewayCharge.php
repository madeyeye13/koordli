<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;

class GatewayCharge extends Model
{
    protected $fillable = [
        'gateway', 'region', 'percentage', 'fixed_fee',
        'cap', 'absorb', 'is_active', 'description',
    ];

    protected $casts = [
        'percentage' => 'decimal:4',
        'fixed_fee'  => 'decimal:2',
        'cap'        => 'decimal:2',
        'absorb'     => 'boolean',
        'is_active'  => 'boolean',
    ];

    /**
     * Calculate the amount to charge tenant so platform receives $desiredAmount after fees.
     * If absorb is false, returns $desiredAmount unchanged.
     */
    public function calculateAbsorbed(float $desiredAmount): float
    {
        if (!$this->absorb) {
            return $desiredAmount;
        }

        $pct   = (float) $this->percentage;
        $fixed = (float) $this->fixed_fee;

        // Formula: chargeAmount = (desiredAmount + fixed) / (1 - percentage)
        $absorbed = ($desiredAmount + $fixed) / (1 - $pct);

        // If cap exists, check if the actual fee exceeds cap
        if ($this->cap) {
            $fee = $absorbed - $desiredAmount;
            if ($fee > (float) $this->cap) {
                $absorbed = $desiredAmount + (float) $this->cap;
            }
        }

        return round($absorbed, 2);
    }

    /**
     * Calculate the actual fee amount for display purposes
     */
    public function calculateFee(float $amount): float
    {
        $fee = ($amount * (float) $this->percentage) + (float) $this->fixed_fee;
        if ($this->cap) {
            $fee = min($fee, (float) $this->cap);
        }
        return round($fee, 2);
    }
}