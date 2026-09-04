<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetItem extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'budget_id',
        'vendor_invoice_id',
        'source',
        'category',
        'estimated',
        'actual',
        'paid',
        'notes',
        'responsible_party',
    ];

    protected $casts = [
        'estimated' => 'decimal:2',
        'actual'    => 'decimal:2',
        'paid'      => 'decimal:2',
    ];

   public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    public function vendorInvoice(): BelongsTo
    {
        return $this->belongsTo(VendorInvoice::class);
    }

    public function isFromVendorInvoice(): bool
    {
        return $this->source === 'vendor_invoice';
    }

    /**
     * NULL means "not yet classified" — treated as 'planner' in every
     * calculation, exactly reproducing pre-existing behavior for any
     * item created before this concept existed, or any planner who
     * never engages with this field at all.
     */
    /**
     * 'client_budget' — paid out of money the client already gave the
     * planner to hold for this event. A real cost, but NOT the
     * planner's own money — never counts against their personal Net
     * Position. This is the correct default for the ordinary case.
     *
     * 'planner_pocket' — genuinely the planner's own money (fronting a
     * deposit before client funds clear, absorbing a shortfall, etc.).
     * Rare, deliberately not the default anywhere in the UI.
     *
     * 'client' — client pays that vendor directly; planner never
     * touches this money at all.
     */
    public function effectiveResponsibleParty(): string
    {
        return $this->responsible_party ?: 'client_budget';
    }

    public function responsiblePartyLabel(): string
    {
        return match($this->effectiveResponsibleParty()) {
            'client'         => 'Client Pays Directly',
            'planner_pocket' => 'Your Own Money',
            default          => "From Client's Budget",
        };
    }
}