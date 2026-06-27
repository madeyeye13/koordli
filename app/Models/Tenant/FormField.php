<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormField extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'form_id', 'tenant_id', 'field_type', 'label',
        'placeholder', 'is_required', 'options', 'settings', 'sort_order',
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
            'date'     => 'Date',
            'file'     => 'File Upload',
            default    => ucfirst($this->field_type),
        };
    }

    public function hasOptions(): bool
    {
        return in_array($this->field_type, ['dropdown', 'radio', 'checkbox']);
    }
}