<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;

class CurrencySetting extends Model
{
    protected $fillable = [
        'code',
        'name',
        'symbol',
        'is_active',
        'gateway_supported',
    ];

    protected $casts = [
        'is_active'         => 'boolean',
        'gateway_supported' => 'array',
    ];
}