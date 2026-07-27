<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventLocation extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'event_id', 'name', 'address', 'date', 'notes', 'sort_order'];

    protected $casts = [
        'date' => 'date',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function runsheetItems(): HasMany
    {
        return $this->hasMany(RunsheetItem::class);
    }
}