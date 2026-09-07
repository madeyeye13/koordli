<?php

namespace App\Livewire\Public\Blog;

use App\Models\Central\BlogCategory;
use App\Models\Central\BlogPost;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.landing')]
#[Title('Blog — Koordli')]
class BlogIndex extends Component
{
    use WithPagination;

    #[Url] public string $q = '';
    #[Url] public string $category = '';
    #[Url] public string $tag = '';

    public function updatedQ(): void { $this->resetPage(); }
    public function updatedCategory(): void { $this->resetPage(); }

    public function render()
    {
        $posts = BlogPost::with(['categories', 'tags'])
            ->where('status', 'published')
            ->where('published_at', '<=', now())
            ->when($this->q, fn($qr) => $qr->where('title', 'like', '%' . $this->q . '%'))
            ->when($this->category, fn($qr) => $qr->whereHas('categories', fn($c) => $c->where('slug', $this->category)))
            ->when($this->tag, fn($qr) => $qr->whereHas('tags', fn($t) => $t->where('slug', $this->tag)))
            ->orderByDesc('published_at')
            ->paginate(9);

        return view('livewire.public.blog.blog-index', [
            'posts'      => $posts,
            'categories' => BlogCategory::orderBy('name')->get(),
        ]);
    }
}