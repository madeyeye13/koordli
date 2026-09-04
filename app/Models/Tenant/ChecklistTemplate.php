<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChecklistTemplate extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'title', 'description', 'industry_profile_id', 'created_by'];

        public function industryProfile(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Central\IndustryProfile::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ChecklistTemplateItem::class)->orderBy('sort_order');
    }
}