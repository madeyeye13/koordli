<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MoodboardSection extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id', 'moodboard_id', 'title',
        'pos_x', 'pos_y', 'width', 'height', 'sort_order',
    ];

    public function moodboard(): BelongsTo
    {
        return $this->belongsTo(Moodboard::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(MoodboardItem::class, 'section_id')->orderBy('sort_order');
    }
}