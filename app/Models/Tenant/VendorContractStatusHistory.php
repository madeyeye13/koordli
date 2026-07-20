<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorContractStatusHistory extends Model
{
    use BelongsToTenant;

    protected $table = 'vendor_contract_status_history';

    protected $fillable = [
        'tenant_id', 'vendor_contract_id', 'from_status', 'to_status', 'changed_by', 'note',
    ];

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function label(): string
    {
        $from = $this->from_status ? ucfirst($this->from_status) : 'Created';
        $to   = ucfirst($this->to_status);
        return "{$from} → {$to}";
    }
}