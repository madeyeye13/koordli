<?php

namespace App\Livewire\Platform\Blog;

use App\Mail\CommentApprovedMail;
use App\Models\Central\BlogComment;
use App\Traits\WithToast;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.platform')]
class BlogCommentModeration extends Component
{
    use WithPagination, WithToast;

    public function mount(): void
    {
        abort_unless(auth('platform')->check(), 403);
    }

    public function approve(int $id): void
    {
        $comment = BlogComment::find($id);
        if (!$comment) return;

        $comment->update(['status' => 'approved']);
        Mail::to($comment->author_email)->send(new CommentApprovedMail($comment));
        $this->toastSuccess('Approved.');
    }

    public function reject(int $id): void
    {
        BlogComment::find($id)?->update(['status' => 'rejected']);
        $this->toastSuccess('Rejected.');
    }

    public function render()
    {
        $comments = BlogComment::with('post')
            ->where('status', 'pending')
            ->orderBy('created_at')
            ->paginate(20);

        return view('livewire.platform.blog.blog-comment-moderation', compact('comments'));
    }
}