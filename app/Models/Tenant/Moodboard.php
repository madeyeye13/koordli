<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Moodboard extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'event_id', 'title', 'description', 'status',
        'cover_document_id', 'is_client_visible',
        'is_template', 'template_source_id', 'industry_profile_id',
        'created_by',
    ];

    protected $casts = [
        'is_client_visible' => 'boolean',
        'is_template'       => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Moodboard $moodboard) {
            if (empty($moodboard->uuid)) {
                $moodboard->uuid = Str::uuid();
            }
        });
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function industryProfile(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Central\IndustryProfile::class);
    }

    public function coverDocument(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'cover_document_id');
    }

    public function templateSource(): BelongsTo
    {
        return $this->belongsTo(Moodboard::class, 'template_source_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(MoodboardItem::class)->orderBy('sort_order');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(MoodboardSection::class)->orderBy('sort_order');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(MoodboardStatusHistory::class)->orderByDesc('created_at');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(MoodboardParticipant::class);
    }

    /**
     * Mirrors VendorContract's own changeStatus() shape exactly — logs
     * every transition to the audit table rather than silently
     * overwriting the status column.
     */
    public function changeStatus(string $status, ?string $note = null): void
    {
        $this->update(['status' => $status]);

        MoodboardStatusHistory::create([
            'tenant_id'    => $this->tenant_id,
            'moodboard_id' => $this->id,
            'status'       => $status,
            'note'         => $note,
            'changed_by'   => auth()->id(),
            'created_at'   => now(),
        ]);
    }

    public function hasParticipant(string $type, int $id): bool
    {
        return $this->participants()
            ->where('participant_type', $type)
            ->where('participant_id', $id)
            ->exists();
    }

    public function statusColor(): string
    {
        return match($this->status) {
            'in_review' => '#F59E0B',
            'approved'  => '#10B981',
            'archived'  => '#A8A29E',
            default     => '#78716C',
        };
    }

    public function statusLabel(): string
    {
        return match($this->status) {
            'in_review' => 'In Review',
            'approved'  => 'Approved',
            'archived'  => 'Archived',
            default     => 'Draft',
        };
    }
}