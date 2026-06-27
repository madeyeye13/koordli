<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class FormSubmission extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'uuid', 'tenant_id', 'form_id', 'source', 'status',
        'assigned_to', 'ip_address', 'user_agent',
        'submitted_at', 'followed_up_at',
    ];

    protected $casts = [
        'submitted_at'  => 'datetime',
        'followed_up_at'=> 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (FormSubmission $submission) {
            if (empty($submission->uuid)) {
                $submission->uuid = Str::uuid();
            }
            if (empty($submission->submitted_at)) {
                $submission->submitted_at = now();
            }
        });
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(FormSubmissionValue::class, 'submission_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(ConsultationBooking::class, 'id', 'submission_id');
    }

    public function getValueFor(int $fieldId): ?string
    {
        return $this->values->firstWhere('field_id', $fieldId)?->value;
    }
}