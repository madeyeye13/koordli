<?php

namespace App\Livewire\Platform\Blog;

use App\Models\Central\BlogCategory;
use App\Models\Central\BlogPost;
use App\Models\Central\BlogTag;
use App\Services\ImageOptimizationService;
use App\Traits\WithToast;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.platform')]
class BlogPostEditor extends Component
{
    use WithToast, WithFileUploads;

    public ?BlogPost $post = null;

    public string $title = '';
    public string $excerpt = '';
    public string $content = '';
    public string $meta_description = '';
    public bool $comments_enabled = true;
    public string $status = 'draft';

    public array $selectedCategoryIds = [];
    public array $selectedTagIds = [];
    public string $newCategoryName = '';
    public string $newTagName = '';

    public $featured_image = null;
    public ?string $featured_image_url = null;

    public function mount(?int $id = null): void
    {
        abort_unless(auth('platform')->check(), 403);

        if ($id) {
            $this->post = BlogPost::with(['categories', 'tags'])->findOrFail($id);
            $this->title = $this->post->title;
            $this->excerpt = $this->post->excerpt ?? '';
            $this->content = $this->post->content;
            $this->meta_description = $this->post->meta_description ?? '';
            $this->comments_enabled = $this->post->comments_enabled;
            $this->status = $this->post->status;
            $this->selectedCategoryIds = $this->post->categories->pluck('id')->toArray();
            $this->selectedTagIds = $this->post->tags->pluck('id')->toArray();
            $this->featured_image_url = $this->post->featuredImageUrl();
        }
    }

    #[Renderless]
    public function updateContent(string $html): void
    {
        $this->content = $html;
    }

    /** Instant, inline creation — usable immediately, no approval step. */
    public function createCategory(): void
    {
        if (!$this->newCategoryName) return;
        $cat = BlogCategory::firstOrCreate(
            ['slug' => Str::slug($this->newCategoryName)],
            ['name' => $this->newCategoryName]
        );
        $this->selectedCategoryIds[] = $cat->id;
        $this->newCategoryName = '';
    }

    public function createTag(): void
    {
        if (!$this->newTagName) return;
        $tag = BlogTag::firstOrCreate(
            ['slug' => Str::slug($this->newTagName)],
            ['name' => $this->newTagName]
        );
        $this->selectedTagIds[] = $tag->id;
        $this->newTagName = '';
    }

    public function toggleCategory(int $id): void
    {
        $this->selectedCategoryIds = in_array($id, $this->selectedCategoryIds)
            ? array_values(array_diff($this->selectedCategoryIds, [$id]))
            : [...$this->selectedCategoryIds, $id];
    }

    public function toggleTag(int $id): void
    {
        $this->selectedTagIds = in_array($id, $this->selectedTagIds)
            ? array_values(array_diff($this->selectedTagIds, [$id]))
            : [...$this->selectedTagIds, $id];
    }

    /** Called the moment a file is selected — image appears in-editor
     *  immediately via a local blob preview on the JS side; this just
     *  handles the real, optimized, persisted copy in the background. */
    public function uploadContentImage()
    {
        $this->validate(['featured_image' => 'nullable']); // guard clause only
        return null; // real inline-image upload handled by uploadEditorImage() below
    }

    public function uploadEditorImage($file): array
    {
        $result = app(ImageOptimizationService::class)->store($file, 'blog/content');
        return ['url' => \Illuminate\Support\Facades\Storage::disk(config('blog.storage_disk'))->url($result['path'])];
    }

    public function uploadFeaturedImage(): void
    {
        if (!$this->featured_image) return;

        $result = app(ImageOptimizationService::class)->store($this->featured_image, 'blog/featured');

        if ($this->post && $this->post->featured_image_path) {
            \Illuminate\Support\Facades\Storage::disk(config('blog.storage_disk'))->delete($this->post->featured_image_path);
        }

        $this->post?->update(['featured_image_path' => $result['path'], 'featured_image_size' => $result['size']]);
        $this->featured_image_url = \Illuminate\Support\Facades\Storage::disk(config('blog.storage_disk'))->url($result['path']);
        $this->featured_image = null;
        $this->toastSuccess('Featured image updated.');
    }

    public function save(string $intent = 'draft'): void
    {
        $this->validate([
            'title'   => 'required|string|min:3|max:200',
            'content' => 'required|string|min:10',
            'meta_description' => 'nullable|string|max:300',
        ]);

        $data = [
            'title'             => $this->title,
            'excerpt'           => $this->excerpt ?: Str::limit(strip_tags($this->content), 160),
            'content'           => $this->content,
            'meta_title'        => $this->title, // automatic, per spec
            'meta_description'  => $this->meta_description ?: Str::limit(strip_tags($this->content), 160),
            'comments_enabled'  => $this->comments_enabled,
            'status'            => $intent === 'publish' ? 'published' : 'draft',
            'author_id'         => auth('platform')->id(),
        ];

        if ($intent === 'publish' && (!$this->post || !$this->post->published_at)) {
            $data['published_at'] = now();
        }

        if ($this->post) {
            $this->post->update($data);
        } else {
            $this->post = BlogPost::create($data);
        }

        $this->post->categories()->sync($this->selectedCategoryIds);
        $this->post->tags()->sync($this->selectedTagIds);

        $this->toastSuccess($intent === 'publish' ? 'Published.' : 'Saved as draft.');
        $this->redirect(route('platform.blog.edit', $this->post->id), navigate: true);
    }

    public function render()
    {
        return view('livewire.platform.blog.blog-post-editor', [
            'categories' => BlogCategory::orderBy('name')->get(),
            'tags'       => BlogTag::orderBy('name')->get(),
        ]);
    }
}