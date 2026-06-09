<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use App\Models\Tenant\Event;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TenantEventStatus extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'color',
        'sort_order',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'status_id');
    }
}