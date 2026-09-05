<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\App;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        // Auto-fill tenant_id on create
        static::creating(function ($model) {
            $tenantId = static::currentTenantId();
            if ($tenantId && empty($model->tenant_id)) {
                $model->tenant_id = $tenantId;
            }
        });

        // Auto-scope all queries to current tenant
        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenantId = static::currentTenantId();
            if ($tenantId) {
                $builder->where(
                    $builder->getModel()->getTable() . '.tenant_id',
                    $tenantId
                );
            }
        });
    }

    protected static function currentTenantId(): ?int
    {
        if (app()->bound('tenant.id')) {
            return (int) app('tenant.id');
        }

        // Resolving the authenticated tenant user here would recursively
        // boot this trait on the User model itself.
        if (is_a(static::class, \App\Models\Tenant\User::class, true)) {
            return null;
        }

        $user = auth('web')->user();
        return $user?->tenant_id ? (int) $user->tenant_id : null;
    }

    public function scopeForTenant(Builder $query, int $tenantId): Builder
    {
        return $query->withoutGlobalScope('tenant')
                     ->where('tenant_id', $tenantId);
    }

    public function scopeWithoutTenant(Builder $query): Builder
    {
        return $query->withoutGlobalScope('tenant');
    }
}