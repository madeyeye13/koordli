<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Checklist extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'event_id', 'is_client_visible', 'created_by'];

    protected $casts = ['is_client_visible' => 'boolean'];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ChecklistItem::class);
    }

    /**
     * Progress grouped by phase — the data shape the client-facing
     * simplified view needs (per-phase completion, not granular item
     * detail). Returns phases in chronological order, only including
     * phases that actually have items.
     */
    public function phaseProgress(): array
    {
        $grouped = $this->items->groupBy('phase');
        $result = [];

        foreach (\App\Enums\ChecklistPhase::cases() as $phase) {
            $items = $grouped->get($phase->value);
            if (!$items || $items->isEmpty()) continue;

            $result[] = [
                'phase'     => $phase,
                'total'     => $items->count(),
                'completed' => $items->where('is_completed', true)->count(),
            ];
        }

        return $result;
    }

    public function overallProgress(): array
    {
        return [
            'total'     => $this->items->count(),
            'completed' => $this->items->where('is_completed', true)->count(),
        ];
    }
}