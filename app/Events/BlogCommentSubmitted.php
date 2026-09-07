<?php
// app/Events/BlogCommentSubmitted.php
namespace App\Events;

use App\Models\Central\BlogComment;
use Illuminate\Foundation\Events\Dispatchable;

class BlogCommentSubmitted
{
    use Dispatchable;
    public function __construct(public BlogComment $comment) {}
}