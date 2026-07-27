<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'asset_category_id', 'name', 'status', 'notes'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id');
    }

    public function eventAssignments(): HasMany
    {
        return $this->hasMany(AssetEventAssignment::class);
    }

    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'asset_event_assignments')
            ->withPivot(['date_from', 'date_to', 'notes'])
            ->withTimestamps();
    }

    public function statusLabel(): string
    {
        return match($this->status) {
            'reserved'    => 'Reserved',
            'maintenance' => 'Maintenance',
            default       => 'Available',
        };
    }

    public function statusColor(): string
    {
        return match($this->status) {
            'reserved'    => '#3B82F6',
            'maintenance' => '#F59E0B',
            default       => '#10B981',
        };
    }
}