<?php
// app/Models/Tenant/EventStoryChapter.php
namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventStoryChapter extends Model
{
    use BelongsToTenant;

    protected $fillable = ['event_id', 'tenant_id', 'title', 'content', 'sort_order', 'last_edited_by_type'];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}