<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorEventAssignment extends Model
{
    use BelongsToTenant;

    protected $table = 'vendor_event_assignments';

    protected $fillable = [
        'tenant_id',
        'vendor_id',
        'event_id',
        'amount_agreed',
        'amount_paid',
        'status',
        'notes',
        'selection_source',
        'client_approval_status',
        'client_approved_at',
        'is_client_visible',
        'client_can_view_pricing',
        'payment_responsibility',
        'disclaimer_acknowledged_at',
    ];

    protected $casts = [
        'amount_agreed'              => 'decimal:2',
        'amount_paid'                => 'decimal:2',
        'client_approved_at'         => 'datetime',
        'is_client_visible'          => 'boolean',
        'client_can_view_pricing'    => 'boolean',
        'disclaimer_acknowledged_at' => 'datetime',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function statusColor(): string
    {
        return match($this->status) {
            'confirmed'  => '#10B981',
            'cancelled'  => '#EF4444',
            default      => '#F59E0B',
        };
    }

    public function statusBadge(): string
    {
        return match($this->status) {
            'confirmed'  => 'krd-badge-green',
            'cancelled'  => 'krd-badge-red',
            default      => 'krd-badge-amber',
        };
    }

    public function balance(): float
    {
        return (float)$this->amount_agreed - (float)$this->amount_paid;
    }

    public function invoices(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(VendorInvoice::class);
    }

    public function reviews(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(VendorReview::class, 'vendor_event_assignment_id');
    }

    public function plannerReview(): ?VendorReview
    {
        return $this->reviews()->where('reviewer_type', 'planner')->first();
    }

    public function clientReview(): ?VendorReview
    {
        return $this->reviews()->where('reviewer_type', 'client')->first();
    }

    public function eventHasEnded(): bool
    {
        return $this->event?->date && $this->event->date->isPast();
    }

    /**
     * Small badge helpers — used on both the tenant Event Detail card and
     * the Vendor Directory, so staff browsing normally (not specifically
     * checking a suggestions inbox) immediately sees which vendors were
     * client-driven. Deliberately returns null for the ordinary
     * planner_selected case (today's default) so nothing new renders for
     * tenants who never use this feature.
     */
    public function selectionBadgeLabel(): ?string
    {
        return match($this->selection_source) {
            'client_selected'   => "Client's Choice",
            'planner_suggested' => 'Suggested by You, Approved',
            'client_external'   => "Client's Own Vendor",
            default              => null,
        };
    }

    public function selectionBadgeColor(): string
    {
        return match($this->selection_source) {
            'client_selected'   => '#7C3AED',
            'planner_suggested' => '#3B82F6',
            'client_external'   => '#F59E0B',
            default              => '#A8A29E',
        };
    }

    public function isPendingClientApproval(): bool
    {
        return $this->client_approval_status === 'pending';
    }

    public function paymentResponsibilityLabel(): string
    {
        return match($this->payment_responsibility) {
            'client_pays_planner'      => 'Client pays via ' . (auth()->user()?->tenant->name ?? 'planner'),
            'client_pays_vendor_direct' => 'Client pays vendor directly',
            default                     => 'Paid from event budget',
        };
    }

    public function needsDisclaimerAcknowledgment(): bool
    {
        return $this->selection_source === 'client_external' && !$this->disclaimer_acknowledged_at;
    }
}