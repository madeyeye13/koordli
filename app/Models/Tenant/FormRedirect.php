<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormRedirect extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'form_id',
        'tenant_id',
        'redirect_type',
        'redirect_url',
        'whatsapp_number',
        'whatsapp_message',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }
}