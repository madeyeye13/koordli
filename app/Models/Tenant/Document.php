<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    use BelongsToTenant;

    protected static function booted(): void
    {
        static::creating(function (Document $document) {
            if (empty($document->uuid)) {
                $document->uuid = \Illuminate\Support\Str::uuid();
            }
        });
    }

    protected $fillable = [
        'uuid',
        'tenant_id',
        'documentable_type',
        'documentable_id',
        'folder_id',
        'name',
        'type', // file|logo|link
        'disk',
        'path',
        'external_url',
        'thumbnail_path',
        'width',
        'height',
        'duration_seconds',
        'compression_status',
        'mime_type',
        'size',
        'uploaded_by_type',
        'uploaded_by_id',
        'uploaded_via_share_link_id',
    ];

    protected $casts = [
        'width'  => 'integer',
        'height' => 'integer',
        'duration_seconds' => 'integer',
    ];

    public function folder(): BelongsTo
    {
        return $this->belongsTo(DocumentFolder::class, 'folder_id');
    }

    public function sharedLink(): BelongsTo
    {
        return $this->belongsTo(DocumentShareLink::class, 'uploaded_via_share_link_id');
    }

    /**
     * Polymorphic uploader — replaces the old hard belongsTo(User) FK.
     * Returns null for anonymous guest-link uploads (uploaded_by_type/id
     * are both null in that case; provenance is tracked instead via
     * uploaded_via_share_link_id).
     */
    public function uploadedBy(): ?Model
    {
        if (!$this->uploaded_by_type || !$this->uploaded_by_id) return null;

        return $this->uploaded_by_type::withoutGlobalScope('tenant')
            ->find($this->uploaded_by_id);
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'image/');
    }

    public function isVideo(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'video/');
    }
}