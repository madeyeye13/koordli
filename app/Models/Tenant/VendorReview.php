<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorReview extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'vendor_id', 'vendor_event_assignment_id', 'event_id',
        'reviewer_type', 'reviewer_id',
        'professionalism', 'communication', 'punctuality',
        'quality_of_service', 'reliability', 'overall_experience',
        'comment', 'is_public', 'is_archived', 'locked_at',
    ];

    protected $casts = [
        'is_public'   => 'boolean',
        'is_archived' => 'boolean',
        'locked_at'   => 'datetime',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(VendorEventAssignment::class, 'vendor_event_assignment_id');
    }

    public function averageScore(): float
    {
        return round((
            $this->professionalism + $this->communication + $this->punctuality +
            $this->quality_of_service + $this->reliability + $this->overall_experience
        ) / 6, 2);
    }

    public function isEditable(): bool
    {
        return $this->locked_at === null || $this->locked_at->isFuture();
    }

    public function reviewerLabel(): string
    {
        return $this->reviewer_type === 'planner' ? 'Event Planner' : 'Client';
    }

    protected static function booted(): void
    {
        static::creating(function (VendorReview $review) {
            if (empty($review->locked_at)) {
                $review->locked_at = now()->addDays(7);
            }
        });

        static::saved(function (VendorReview $review) {
            $review->vendor?->recalculateRating();
        });
    }
}