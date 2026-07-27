<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetCategory extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'name', 'icon', 'sort_order'];

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }
}