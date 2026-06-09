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
            if (app()->bound('tenant.id') && empty($model->tenant_id)) {
                $model->tenant_id = app('tenant.id');
            }
        });

        // Auto-scope all queries to current tenant
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (app()->bound('tenant.id')) {
                $builder->where(
                    $builder->getModel()->getTable() . '.tenant_id',
                    app('tenant.id')
                );
            }
        });
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