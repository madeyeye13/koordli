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
        'fee_type', 'fee_amount', 'fee_note', 'fee_source',
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

    // Vendor/cost side — unchanged, still the full picture of every
    // cost line item regardless of who's paying it.
    public function totalEstimated(): float { return (float) $this->items->sum('estimated'); }
    public function totalActual(): float    { return (float) $this->items->sum('actual'); }
    public function totalVendorPaid(): float { return (float) $this->items->sum('paid'); }
    public function totalVendorBalance(): float { return $this->totalActual() - $this->totalVendorPaid(); }

    /**
     * Only costs the PLANNER is actually on the hook for — items marked
     * responsible_party = 'client'/'other' are excluded, since those
     * were never the planner's money to begin with. Items with no
     * classification default to 'planner', reproducing old behavior
     * exactly for anyone who never touches this field.
     */
    /**
     * Only 'planner_pocket' items count — genuinely the planner's own
     * money at risk. 'client_budget' items are real costs, correctly
     * shown everywhere else on the page, but they were never the
     * planner's own money, so they don't touch this figure.
     */
    public function plannerBorneCost(): float
    {
        return (float) $this->items
            ->filter(fn($item) => $item->effectiveResponsibleParty() === 'planner_pocket')
            ->sum('actual');
    }

    public function plannerBornePaid(): float
    {
        return (float) $this->items
            ->filter(fn($item) => $item->effectiveResponsibleParty() === 'planner_pocket')
            ->sum('paid');
    }

    // Client side
    public function agreedBudget(): float   { return (float) ($this->event->agreed_budget ?? 0); }
    public function totalClientPaid(): float { return (float) $this->clientPayments->sum('amount'); }
    public function clientOutstanding(): float { return max(0, $this->agreedBudget() - $this->totalClientPaid()); }

    /**
     * Only payments explicitly tagged toward the planner's fee — NOT
     * every client payment. A payment with no tag defaults to
     * 'unspecified', excluded here (it's not confirmed fee revenue),
     * matching the honest, conservative reading of ambiguous old data.
     */
    public function feeRevenueCollected(): float
    {
        return (float) $this->clientPayments
            ->filter(fn($p) => $p->effectivePurpose() === 'planner_fee')
            ->sum('amount');
    }

    public function feeOutstanding(): float
    {
        return max(0, (float) ($this->fee_amount ?? 0) - $this->feeRevenueCollected());
    }

    /**
     * What the client owes in total. If the fee sits ON TOP of the
     * budget, it's added to what they already agreed to pay. If it
     * comes FROM the budget, the client's total never changes — the
     * fee is just carved out of money they were already paying.
     */
    public function totalClientCommitment(): float
    {
        if ($this->fee_source === 'on_top') {
            return $this->agreedBudget() + (float) ($this->fee_amount ?? 0);
        }
        return $this->agreedBudget();
    }

    /**
     * Only meaningful when fee_source = 'from_budget' — how much of the
     * agreed budget is actually left to spend on real event costs once
     * the fee is set aside. Returns the full budget when no fee is
     * configured, or when the fee sits on top (nothing carved out).
     */
    public function availableForEventCosts(): float
    {
        if ($this->fee_source === 'from_budget') {
            return max(0, $this->agreedBudget() - (float) ($this->fee_amount ?? 0));
        }
        return $this->agreedBudget();
    }

    /**
     * REPLACES the old single grossProfit() figure, which conflated
     * every client payment with revenue and every cost with a planner
     * expense — wrong for any model where a client pays a vendor
     * directly. This is now two honest, separately-labeled numbers.
     * A planner who never uses fee_amount or responsible_party sees
     * feeRevenueCollected() = 0 and plannerBorneCost() = totalActual()
     * (since every item defaults to 'planner') — i.e., this degrades
     * gracefully to something reasonable even with zero new data entered.
     */
    public function plannerNetPosition(): float
    {
        return $this->feeRevenueCollected() - $this->plannerBorneCost();
    }

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