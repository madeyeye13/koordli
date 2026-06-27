<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'price',
        'billing_cycle',
        'trial_days',
        'features',
        'limits',
        'is_active',
        'is_featured',
    ];

    protected $casts = [
        'features'    => 'array',
        'limits'      => 'array',
        'is_active'   => 'boolean',
        'is_featured' => 'boolean',
    ];
    
    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    public function hasFeature(string $key): bool
    {
        return in_array($key, $this->features ?? []);
    }

    public function getLimit(string $key): mixed
    {
        return $this->limits[$key] ?? null;
    }
}