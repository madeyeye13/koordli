<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TenantTaskCategory extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'color',
        'icon',
        'sort_order',
    ];

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'task_category_id');
    }
}