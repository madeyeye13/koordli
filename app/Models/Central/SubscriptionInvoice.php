<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class SubscriptionInvoice extends Model
{
    protected $fillable = [
        'uuid', 'tenant_id', 'subscription_id', 'gateway',
        'gateway_invoice_id', 'amount', 'amount_ngn', 'exchange_rate',
        'currency', 'billing_cycle', 'status', 'paid_at',
        'invoice_url', 'metadata',
    ];

    protected $casts = [
        'paid_at'   => 'datetime',
        'metadata'  => 'array',
        'amount'    => 'decimal:2',
        'amount_ngn'=> 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (SubscriptionInvoice $invoice) {
            if (empty($invoice->uuid)) {
                $invoice->uuid = Str::uuid();
            }
        });
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}