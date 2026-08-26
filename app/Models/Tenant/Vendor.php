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
        'source',
        'added_by_client_id',
        'name',
        'contact_name',
        'phone',
        'email',
        'website',
        'instagram',
        'description',
        'notes',
        'rating',
        'planner_rating_avg',
        'client_rating_avg',
        'weighted_rating',
        'completed_events_count',
        'reviews_count',
        'is_preferred',
        'is_active',
        // Marketplace-ready (future Global Vendor Marketplace — inert for now)
        'is_public',
        'slug',
        'city',
        'country',
        'portfolio_images',
        'public_description',
        'marketplace_views',
        'marketplace_inquiries',
    ];

    protected $casts = [
        'is_preferred'      => 'boolean',
        'is_active'         => 'boolean',
        'rating'            => 'integer',
        'is_public'         => 'boolean',
        'portfolio_images'  => 'array',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(VendorCategory::class, 'vendor_category_id');
    }

    public function addedByClient(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Central\Client::class, 'added_by_client_id');
    }

    public function isClientAdded(): bool
    {
        return $this->source === 'client_added';
    }

    public function eventAssignments(): HasMany
    {
        return $this->hasMany(VendorEventAssignment::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(VendorReview::class)->where('is_archived', false);
    }

    public function recalculateRating(): void
    {
        $reviews = $this->reviews()->get();

        if ($reviews->isEmpty()) {
            $this->update([
                'planner_rating_avg'      => null,
                'client_rating_avg'       => null,
                'weighted_rating'         => null,
                'rating'                  => null,
                'reviews_count'           => 0,
            ]);
            return;
        }

        $plannerReviews = $reviews->where('reviewer_type', 'planner');
        $clientReviews  = $reviews->where('reviewer_type', 'client');

        $plannerAvg = $plannerReviews->isNotEmpty()
            ? round($plannerReviews->map->averageScore()->avg(), 2)
            : null;

        $clientAvg = $clientReviews->isNotEmpty()
            ? round($clientReviews->map->averageScore()->avg(), 2)
            : null;

        // Weighted: planner 80%, client 20%. If only one type exists, use that type's average alone.
        if ($plannerAvg !== null && $clientAvg !== null) {
            $weighted = round(($plannerAvg * 0.8) + ($clientAvg * 0.2), 2);
        } elseif ($plannerAvg !== null) {
            $weighted = $plannerAvg;
        } else {
            $weighted = $clientAvg;
        }

        $this->update([
            'planner_rating_avg' => $plannerAvg,
            'client_rating_avg'  => $clientAvg,
            'weighted_rating'    => $weighted,
            'rating'             => (int) round($weighted),
            'reviews_count'      => $reviews->count(),
        ]);
    }

    public function categoryAverages(): array
    {
        $reviews = $this->reviews;
        if ($reviews->isEmpty()) return [];

        return [
            'professionalism'    => round($reviews->avg('professionalism'), 1),
            'communication'      => round($reviews->avg('communication'), 1),
            'punctuality'        => round($reviews->avg('punctuality'), 1),
            'quality_of_service' => round($reviews->avg('quality_of_service'), 1),
            'reliability'        => round($reviews->avg('reliability'), 1),
            'overall_experience' => round($reviews->avg('overall_experience'), 1),
        ];
    }

    public function unavailableDates(): HasMany
    {
        return $this->hasMany(VendorUnavailableDate::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(VendorContract::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(VendorInvoice::class);
    }

    public function totalInvoiced(): float
    {
        return (float) $this->invoices()->sum('total_amount');
    }

    public function totalOutstanding(): float
    {
        return $this->invoices->sum(fn($inv) => $inv->balance());
    }

    /**
     * Check if vendor is unavailable on a specific date (personal block)
     */
    public function isUnavailableOn(string $date): bool
    {
        return $this->unavailableDates()
            ->where('date_from', '<=', $date)
            ->where('date_to', '>=', $date)
            ->exists();
    }

    /**
     * Check if vendor is already assigned to another event on the same date
     * Returns the conflicting event names, or empty collection if none
     */
    public function conflictingAssignments(?string $eventDate, ?int $excludeEventId = null): \Illuminate\Support\Collection
    {
        if (!$eventDate) return collect();

        return $this->eventAssignments()
            ->with('event')
            ->whereHas('event', function ($q) use ($eventDate, $excludeEventId) {
                $q->whereDate('date', $eventDate);
                if ($excludeEventId) {
                    $q->where('id', '!=', $excludeEventId);
                }
            })
            ->where('status', '!=', 'cancelled')
            ->get()
            ->pluck('event.name');
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

    /**
     * Marketplace readiness check — a vendor must meet these criteria
     * before they can be listed publicly (used by future Marketplace phase).
     */
    public function isMarketplaceReady(): bool
    {
        return $this->is_active
            && !empty($this->public_description)
            && !empty($this->city)
            && !empty($this->country)
            && $this->vendor_category_id !== null;
    }

    public static function generateMarketplaceSlug(string $name, string $city): string
    {
        return \Illuminate\Support\Str::slug($name . '-' . $city);
    }

}