<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetEventAssignment extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'asset_id', 'event_id', 'date_from', 'date_to', 'notes'];

    protected $casts = [
        'date_from' => 'date',
        'date_to'   => 'date',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}