<?php
// app/Models/Tenant/EventGalleryImage.php
namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class EventGalleryImage extends Model
{
    use BelongsToTenant;

    protected $fillable = ['event_id', 'tenant_id', 'image_path', 'image_size', 'caption', 'uploaded_by_type', 'sort_order'];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function url(): string
    {
        return Storage::disk(config('blog.storage_disk'))->url($this->image_path);
    }
}