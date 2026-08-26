<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MoodboardItem extends Model
{
        use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id', 'moodboard_id', 'section_id', 'type',
        'pos_x', 'pos_y', 'width', 'height', 'sort_order',
        'data', 'document_id', 'linked_type', 'linked_id', 'created_by',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function moodboard(): BelongsTo
    {
        return $this->belongsTo(Moodboard::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(MoodboardSection::class, 'section_id');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Resolves the light, optional pointer — a Vendor, VendorEventAssignment,
     * or Task — never eager-loaded automatically, since this is meant to
     * stay a cheap reference, not a coupling that forces extra queries on
     * every board load.
     */
    public function resolveLinkedRecord(): ?Model
    {
        if (!$this->linked_type || !$this->linked_id) return null;

        return match($this->linked_type) {
            'vendor'            => Vendor::find($this->linked_id),
            'vendor_assignment' => VendorEventAssignment::find($this->linked_id),
            'task'              => Task::find($this->linked_id),
            default             => null,
        };
    }

    public function isImage(): bool { return $this->type === 'image'; }
    public function isText(): bool  { return $this->type === 'text'; }
    public function isColor(): bool { return $this->type === 'color'; }
    public function isLink(): bool  { return $this->type === 'link'; }
    public function isFile(): bool  { return $this->type === 'file'; }
    public function isEmpty(): bool { return $this->type === 'empty'; }
    public function isNote(): bool  { return $this->type === 'note'; }
}