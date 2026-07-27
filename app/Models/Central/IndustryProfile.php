<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IndustryProfile extends Model
{
    protected $fillable = [
        'key', 'name', 'icon', 'description', 'terminology',
        'default_event_types', 'default_vendor_categories', 'default_task_categories',
        'default_roles', 'recommended_feature_flags', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'terminology'               => 'array',
        'default_event_types'       => 'array',
        'default_vendor_categories' => 'array',
        'default_task_categories'   => 'array',
        'default_roles'             => 'array',
        'recommended_feature_flags' => 'array',
        'is_active'                 => 'boolean',
    ];

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    public function term(string $key, string $default): string
    {
        return $this->terminology[$key] ?? $default;
    }
}