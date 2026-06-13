<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientPayment extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'budget_id',
        'amount',
        'description',
        'paid_on',
        'payment_method',
    ];

    protected $casts = [
        'amount'  => 'decimal:2',
        'paid_on' => 'date',
    ];

    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }
}