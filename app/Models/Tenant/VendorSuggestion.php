<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorSuggestion extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'event_id',
        'suggested_by_type', 'suggested_by_id',
        'name', 'category_label', 'vendor_category_id', 'portfolio_link', 'notes',
        'status',
        'decided_by_type', 'decided_by_id', 'decision_note', 'decided_at',
        'resulting_vendor_id', 'resulting_assignment_id',
    ];

    protected $casts = [
        'decided_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(VendorCategory::class, 'vendor_category_id');
    }

    public function resultingVendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'resulting_vendor_id');
    }

    public function resultingAssignment(): BelongsTo
    {
        return $this->belongsTo(VendorEventAssignment::class, 'resulting_assignment_id');
    }

    public function isSuggestedByClient(): bool
    {
        return $this->suggested_by_type === \App\Models\Central\Client::class;
    }

    public function isSuggestedByStaff(): bool
    {
        return $this->suggested_by_type === User::class;
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function suggesterName(): string
    {
        if ($this->isSuggestedByClient()) {
            return \App\Models\Central\Client::withoutGlobalScope('tenant')->find($this->suggested_by_id)?->name ?? 'Client';
        }
        return User::withoutGlobalScope('tenant')->find($this->suggested_by_id)?->name ?? 'Staff';
    }

    public function statusColor(): string
    {
        return match($this->status) {
            'approved' => '#10B981',
            'rejected' => '#EF4444',
            'withdrawn' => '#A8A29E',
            default     => '#F59E0B',
        };
    }
}