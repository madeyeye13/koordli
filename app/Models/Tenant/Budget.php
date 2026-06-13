<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Budget extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'event_id', 'total_amount', 'client_paid', 'currency',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'client_paid'  => 'decimal:2',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BudgetItem::class);
    }

    public function clientPayments(): HasMany
    {
        return $this->hasMany(ClientPayment::class);
    }

    // Vendor side
    public function totalEstimated(): float { return (float) $this->items->sum('estimated'); }
    public function totalActual(): float    { return (float) $this->items->sum('actual'); }
    public function totalVendorPaid(): float { return (float) $this->items->sum('paid'); }
    public function totalVendorBalance(): float { return $this->totalActual() - $this->totalVendorPaid(); }

    // Client side
    public function agreedBudget(): float   { return (float) ($this->event->agreed_budget ?? 0); }
    public function totalClientPaid(): float { return (float) $this->clientPayments->sum('amount'); }
    public function clientOutstanding(): float { return max(0, $this->agreedBudget() - $this->totalClientPaid()); }

    // Profit
    public function grossProfit(): float    { return $this->totalClientPaid() - $this->totalActual(); }
    public function projectedProfit(): float { return $this->agreedBudget() - $this->totalEstimated(); }

    // Progress
    public function spentPercentage(): float
    {
        if ($this->agreedBudget() <= 0) return 0;
        return min(100, round(($this->totalActual() / $this->agreedBudget()) * 100, 1));
    }

    public function collectedPercentage(): float
    {
        if ($this->agreedBudget() <= 0) return 0;
        return min(100, round(($this->totalClientPaid() / $this->agreedBudget()) * 100, 1));
    }

    public function variance(): float { return $this->agreedBudget() - $this->totalEstimated(); }
}