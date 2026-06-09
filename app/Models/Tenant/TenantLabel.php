<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TenantLabel extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'color',
        'entity_type',
    ];

    public function assignments(): HasMany
    {
        return $this->hasMany(LabelAssignment::class, 'label_id');
    }
}