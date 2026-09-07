<?php

namespace App\Livewire\Public\Blog;

use App\Models\Central\BlogComment;
use App\Models\Central\BlogPost;
use App\Events\BlogCommentSubmitted;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.landing')]
class BlogShow extends Component
{
    public BlogPost $post;
    public $recentPosts;

    public string $commentName = '';
    public string $commentEmail = '';
    public string $commentBody = '';
    public bool $commentSubmitted = false;

    public function mount(string $slug): void
    {
        $this->post = BlogPost::with(['categories', 'tags', 'approvedComments'])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->where('published_at', '<=', now())
            ->firstOrFail();

        $this->recentPosts = BlogPost::where('status', 'published')
            ->where('published_at', '<=', now())
            ->where('id', '!=', $this->post->id)
            ->orderByDesc('published_at')
            ->limit(4)
            ->get();
    }

    public function getTitle(): string
    {
        return $this->post->effectiveMetaTitle();
    }

    public function submitComment(): void
    {
        if (!$this->post->comments_enabled) return;

        $this->validate([
            'commentName'  => 'required|string|max:100',
            'commentEmail' => 'required|email|max:150',
            'commentBody'  => 'required|string|max:2000',
        ]);

        $comment = BlogComment::create([
            'blog_post_id' => $this->post->id,
            'author_name'  => $this->commentName,
            'author_email' => $this->commentEmail,
            'body'         => $this->commentBody,
            'status'       => 'pending',
            'ip_address'   => request()->ip(),
        ]);

        event(new BlogCommentSubmitted($comment));

        $this->reset(['commentName', 'commentEmail', 'commentBody']);
        $this->commentSubmitted = true;
    }

    public function render()
    {
        return view('livewire.public.blog.blog-show');
    }
}