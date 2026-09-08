<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class RsvpForm extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'uuid', 'tenant_id', 'event_id', 'title', 'slug',
        'meta_description', 'og_image', 'deadline', 'guest_limit',
        'branding', 'system_questions', 'ticket_settings',
        'is_active', 'created_by',
    ];

    protected $casts = [
        'branding'        => 'array',
        'system_questions' => 'array',
        'ticket_settings' => 'array',
        'deadline'        => 'datetime',
        'is_active'       => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (RsvpForm $form) {
            if (empty($form->uuid)) {
                $form->uuid = Str::uuid();
            }
        });
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(RsvpResponse::class);
    }

    public function customQuestions(): HasMany
    {
        return $this->hasMany(RsvpQuestion::class)->orderBy('sort_order');
    }

    public function confirmedResponses(): HasMany
    {
        return $this->hasMany(RsvpResponse::class)->where('status', 'confirmed');
    }

    public function totalAttendees(): int
    {
        return $this->confirmedResponses()->sum('plus_one_count') +
               $this->confirmedResponses()->count();
    }

    public function isDeadlinePassed(): bool
    {
        return $this->deadline && now()->isAfter($this->deadline);
    }

    public function isAtCapacity(): bool
    {
        if (!$this->guest_limit) return false;
        return $this->totalAttendees() >= $this->guest_limit;
    }

        public static function defaultSystemQuestions(): array
    {
        return [
                'name' => ['label' => 'Full Name', 'enabled' => true, 'required' => true, 'field_type' => 'text'],
                'email' => ['label' => 'Email Address', 'enabled' => true, 'required' => false, 'field_type' => 'email'],
                'status' => ['label' => 'Will You Be Attending?', 'enabled' => true, 'required' => true, 'field_type' => 'yes_no'],
                'plus_one_count' => ['label' => 'Additional Guests', 'enabled' => true, 'required' => false, 'field_type' => 'number'],
        ];
    }

    /**
     * Resolves against the TENANT'S OWN domain when one is configured
     * and verified (custom_domain, domain_status = 'verified'), falling
     * back to their subdomain, then to Koordli's own app.url — never
     * hardcoded to one domain regardless of which tenant owns this form.
     */
    public function publicUrl(): string
    {
        $tenant = \App\Models\Central\Tenant::find($this->tenant_id);
        $base = $this->resolveTenantBaseUrl($tenant);

        return rtrim($base, '/') . '/rsvp/' . $this->slug;
    }

    private function resolveTenantBaseUrl(?\App\Models\Central\Tenant $tenant): string
    {
        if (!$tenant) return config('app.url');

        if ($tenant->custom_domain && $tenant->domain_status === 'verified') {
            return 'https://' . $tenant->custom_domain;
        }

        if ($tenant->subdomain) {
            $appHost = parse_url(config('app.url'), PHP_URL_HOST);
            $scheme  = parse_url(config('app.url'), PHP_URL_SCHEME) ?? 'https';
            return $scheme . '://' . $tenant->subdomain . '.' . $appHost;
        }

        return config('app.url');
    }
}