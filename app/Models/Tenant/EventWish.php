<?php
// app/Models/Tenant/EventWish.php
namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventWish extends Model
{
    use BelongsToTenant;

    protected $fillable = ['event_id', 'tenant_id', 'guest_name', 'guest_email', 'message', 'status', 'approved_by_type'];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(EventWishReaction::class, 'wish_id');
    }

    public function isApproved(): bool { return $this->status === 'approved'; }
    public function isPending(): bool  { return $this->status === 'pending'; }
}