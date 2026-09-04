<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientPayment extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'budget_id',
        'amount',
        'description',
        'paid_on',
        'payment_method',
        'purpose',
    ];

    protected static function booted(): void
    {
        static::created(function (ClientPayment $payment) {
            if ($payment->effectivePurpose() !== 'planner_fee') return;

            $budget = $payment->budget;
            if (!$budget || !$budget->fee_amount) return;

            $priorFeePaid = ClientPayment::where('budget_id', $budget->id)
                ->where('purpose', 'planner_fee')
                ->where('id', '!=', $payment->id)
                ->sum('amount');

            $newFeePaid = $priorFeePaid + $payment->amount;
            $feeAmount  = (float) $budget->fee_amount;

            // Only fires on the exact transition from "not yet fully
            // paid" to "fully paid" — never re-fires on payments made
            // after the fee is already settled.
            if ($priorFeePaid < $feeAmount && $newFeePaid >= $feeAmount) {
                event(new \App\Events\PlannerFeeFullyCollected($budget));
            }
        });
    }

    protected $casts = [
        'amount'  => 'decimal:2',
        'paid_on' => 'date',
    ];

    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    public function effectivePurpose(): string
    {
        return $this->purpose ?: 'unspecified';
    }

    public function purposeLabel(): string
    {
        return match($this->effectivePurpose()) {
            'planner_fee' => 'Planner Fee',
            'event_costs' => 'Event Costs',
            default       => 'General',
        };
    }
}