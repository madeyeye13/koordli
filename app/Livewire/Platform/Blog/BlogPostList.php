<?php

namespace App\Livewire\Platform\Blog;

use App\Models\Central\BlogPost;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.platform')]
class BlogPostList extends Component
{
    use WithPagination, WithToast;

    #[Url] public string $search = '';
    #[Url] public string $statusFilter = '';

    public function mount(): void
    {
        abort_unless(auth('platform')->check(), 403);
    }

    public bool $showDeleteModal = false;
    public ?int $deleteId = null;

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedStatusFilter(): void { $this->resetPage(); }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        $post = BlogPost::find($this->deleteId);
        if ($post) {
            if ($post->featured_image_path) {
                \Illuminate\Support\Facades\Storage::disk(config('blog.storage_disk'))->delete($post->featured_image_path);
            }
            $post->delete();
            $this->toastSuccess('Post deleted.');
        }
        $this->showDeleteModal = false;
        $this->deleteId = null;
    }

    public function togglePublish(int $id): void
    {
        $post = BlogPost::find($id);
        if (!$post) return;

        if ($post->status === 'published') {
            $post->update(['status' => 'draft']);
            $this->toastSuccess('Unpublished.');
        } else {
            $post->update([
                'status'       => 'published',
                'published_at' => $post->published_at ?? now(),
            ]);
            $this->toastSuccess('Published.');
        }
    }

    public function render()
    {
        $posts = BlogPost::with(['categories', 'author'])
            ->when($this->search, fn($q) => $q->where('title', 'like', '%' . $this->search . '%'))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('livewire.platform.blog.blog-post-list', compact('posts'));
    }
}