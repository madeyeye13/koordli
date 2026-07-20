<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    protected $fillable = [
        'tenant_id', 'plan_id', 'status',
        'trial_ends_at', 'current_period_start', 'current_period_end',
        'expires_at', 'grace_until', 'billing_cycle',
        'cancelled_at', 'gateway', 'gateway_subscription_id',
        'gateway_customer_id', 'currency', 'amount',
        'reminder_14_sent', 'reminder_3_sent',
    ];

    protected $casts = [
        'trial_ends_at'        => 'datetime',
        'current_period_start' => 'datetime',
        'current_period_end'   => 'datetime',
        'expires_at'           => 'datetime',
        'grace_until'          => 'datetime',
        'cancelled_at'         => 'datetime',
        'reminder_14_sent'     => 'boolean',
        'reminder_3_sent'      => 'boolean',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(SubscriptionInvoice::class);
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['active', 'trial']);
    }

    public function isTrialing(): bool
    {
        return $this->status === 'trial'
            && $this->trial_ends_at
            && $this->trial_ends_at->isFuture();
    }

    public function isExpired(): bool
    {
        if ($this->expires_at && $this->expires_at->isPast()) return true;
        if ($this->status === 'trial' && $this->trial_ends_at?->isPast()) return true;
        return false;
    }

    public function isInGracePeriod(): bool
    {
        return $this->isExpired()
            && $this->grace_until
            && $this->grace_until->isFuture();
    }

    public function isLocked(): bool
    {
        return $this->isExpired() && !$this->isInGracePeriod();
    }

    public function trialDaysRemaining(): int
    {
        if (!$this->trial_ends_at) return 0;
        return max(0, (int) now()->diffInDays($this->trial_ends_at, false));
    }

    public function daysUntilExpiry(): int
    {
        $expiry = $this->expires_at ?? $this->trial_ends_at;
        if (!$expiry) return 0;
        return max(0, (int) now()->diffInDays($expiry, false));
    }
}