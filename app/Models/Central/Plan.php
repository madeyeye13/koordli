<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'name', 'slug', 'price', 'billing_cycle', 'trial_days',
        'features', 'limits', 'is_active', 'is_featured',
        'annual_discount_percent', 'allowed_cycles',
    ];

    protected $casts = [
        'features'               => 'array',
        'limits'                 => 'array',
        'allowed_cycles'         => 'array',
        'is_active'              => 'boolean',
        'is_featured'            => 'boolean',
        'annual_discount_percent'=> 'decimal:2',
    ];

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(PlanPrice::class);
    }

    public function hasFeature(string $key): bool
    {
        return in_array($key, $this->features ?? []);
    }

    public function getLimit(string $key): mixed
    {
        return $this->limits[$key] ?? null;
    }

    public function allowsMonthly(): bool
    {
        $cycles = $this->allowed_cycles ?? ['monthly', 'annual'];
        return in_array('monthly', $cycles);
    }

    public function allowsAnnual(): bool
    {
        $cycles = $this->allowed_cycles ?? ['monthly', 'annual'];
        return in_array('annual', $cycles);
    }

    public function getPriceFor(string $currency, string $cycle = 'monthly'): ?PlanPrice
    {
        return $this->prices()
            ->where('currency', $currency)
            ->where('billing_cycle', $cycle)
            ->where('is_active', true)
            ->first();
    }
}