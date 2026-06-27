<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Form extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'uuid', 'tenant_id', 'name', 'slug', 'type', 'status',
        'description', 'hero_image', 'hero_image_path',
        'endpoint_token', 'tenant_email', 'tenant_phone', 'tenant_address',
        'consultation_type', 'location', 'duration_minutes',
        'whatsapp_enabled', 'settings', 'created_by',
    ];

    protected $casts = [
        'settings'          => 'array',
        'whatsapp_enabled'  => 'boolean',
        'duration_minutes'  => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Form $form) {
            if (empty($form->uuid)) {
                $form->uuid = Str::uuid();
            }
            if (empty($form->endpoint_token)) {
                $form->endpoint_token = Str::random(32);
            }
            if (empty($form->created_by)) {
                $form->created_by = auth()->id();
            }
        });
    }

    public function fields(): HasMany
    {
        return $this->hasMany(FormField::class)->orderBy('sort_order');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class);
    }

    public function redirect(): HasOne
    {
        return $this->hasOne(FormRedirect::class);
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(ConsultationAvailability::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(ConsultationBooking::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function publicUrl(): string
    {
        return $this->type === 'consultation'
            ? url('/consult/' . $this->slug)
            : url('/book/' . $this->slug);
    }

    public function endpointUrl(): string
    {
        return url('/api/forms/' . $this->endpoint_token . '/submit');
    }

    public function embedCode(): string
    {
        $url = $this->publicUrl();
        return "<iframe src=\"{$url}\" width=\"100%\" height=\"800\" frameborder=\"0\" style=\"border:none;border-radius:8px;\"></iframe>";
    }
}