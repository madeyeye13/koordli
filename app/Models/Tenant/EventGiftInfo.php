<?php
// app/Models/Tenant/EventGiftInfo.php
namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventGiftInfo extends Model
{
    use BelongsToTenant;

    protected $table = 'event_gift_info';

    protected $fillable = ['event_id', 'tenant_id', 'note', 'bank_accounts', 'registry_links'];

    protected $casts = [
        'bank_accounts'  => 'array',
        'registry_links' => 'array',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}