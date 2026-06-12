<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormField extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'form_id',
        'tenant_id',
        'field_type',
        'label',
        'placeholder',
        'is_required',
        'options',
        'settings',
        'sort_order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'options'     => 'array',
        'settings'    => 'array',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function submissionValues(): HasMany
    {
        return $this->hasMany(FormSubmissionValue::class, 'field_id');
    }
}