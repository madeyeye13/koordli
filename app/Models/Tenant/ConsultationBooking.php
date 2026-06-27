<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ConsultationBooking extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'uuid', 'tenant_id', 'form_id', 'submission_id',
        'booking_date', 'booking_time', 'consultation_type',
        'status', 'guest_name', 'guest_email', 'guest_phone',
        'meeting_link', 'notes',
    ];

    protected $casts = [
        'booking_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (ConsultationBooking $booking) {
            if (empty($booking->uuid)) {
                $booking->uuid = Str::uuid();
            }
        });
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(FormSubmission::class);
    }
}