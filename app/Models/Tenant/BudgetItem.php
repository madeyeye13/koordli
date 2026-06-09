<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetItem extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'budget_id',
        'category',
        'estimated',
        'actual',
        'paid',
        'notes',
    ];

    protected $casts = [
        'estimated' => 'decimal:2',
        'actual'    => 'decimal:2',
        'paid'      => 'decimal:2',
    ];

    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }
}