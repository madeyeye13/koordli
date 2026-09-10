<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventWishReaction extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'wish_id',
        'tenant_id',
        'reaction_type',
        'reactor_token',
    ];

    public function wish(): BelongsTo
    {
        return $this->belongsTo(EventWish::class, 'wish_id');
    }
}
