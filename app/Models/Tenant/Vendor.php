<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'vendor_category_id',
        'name',
        'contact_name',
        'phone',
        'email',
        'website',
        'instagram',
        'description',
        'notes',
        'rating',
        'is_preferred',
        'is_active',
    ];

    protected $casts = [
        'is_preferred' => 'boolean',
        'is_active'    => 'boolean',
        'rating'       => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(VendorCategory::class, 'vendor_category_id');
    }

    public function eventAssignments(): HasMany
    {
        return $this->hasMany(VendorEventAssignment::class);
    }

    public function events(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'vendor_event_assignments')
                    ->withPivot(['amount_agreed', 'amount_paid', 'status', 'notes'])
                    ->withTimestamps();
    }

    public function ratingStars(): string
    {
        if (!$this->rating) return '—';
        return str_repeat('★', $this->rating) . str_repeat('☆', 5 - $this->rating);
    }
}