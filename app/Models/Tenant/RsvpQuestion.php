<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RsvpQuestion extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'rsvp_form_id', 'label',
        'field_type', 'is_required', 'options', 'sort_order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'options'     => 'array',
    ];

    public function rsvpForm(): BelongsTo
    {
        return $this->belongsTo(RsvpForm::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(RsvpResponseAnswer::class);
    }

    public function fieldTypeLabel(): string
    {
        return match($this->field_type) {
            'text'     => 'Short Text',
            'textarea' => 'Long Text',
            'email'    => 'Email',
            'phone'    => 'Phone',
            'number'   => 'Number',
            'dropdown' => 'Dropdown',
            'checkbox' => 'Checkbox',
            'radio'    => 'Radio',
            'yes_no'   => 'Yes / No',
            'date'     => 'Date',
            default    => ucfirst($this->field_type),
        };
    }

    public function hasOptions(): bool
    {
        return in_array($this->field_type, ['dropdown', 'radio', 'checkbox']);
    }
}