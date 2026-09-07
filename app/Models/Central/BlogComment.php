<?php
// app/Models/Central/BlogComment.php
namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlogComment extends Model
{
    protected $fillable = ['blog_post_id', 'author_name', 'author_email', 'body', 'status', 'ip_address'];

    public function post(): BelongsTo
    {
        return $this->belongsTo(BlogPost::class);
    }

    public function isApproved(): bool { return $this->status === 'approved'; }
    public function isPending(): bool  { return $this->status === 'pending'; }
}