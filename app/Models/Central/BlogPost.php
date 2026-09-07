<?php
// app/Models/Central/BlogPost.php
namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class BlogPost extends Model
{
    protected $fillable = [
        'uuid', 'title', 'slug', 'excerpt', 'content',
        'featured_image_path', 'featured_image_size',
        'meta_title', 'meta_description',
        'status', 'published_at', 'comments_enabled', 'author_id',
    ];

    protected $casts = [
        'published_at'     => 'datetime',
        'comments_enabled' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (BlogPost $post) {
            if (empty($post->uuid)) $post->uuid = Str::uuid();

            if (empty($post->slug)) {
                $post->slug = static::generateUniqueSlug($post->title);
            }
        });
    }

    public static function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i = 1;

        while (
            static::where('slug', $slug)
                ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base . '-' . (++$i);
        }

        return $slug;
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(BlogCategory::class, 'blog_post_category');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(BlogTag::class, 'blog_post_tag');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(BlogComment::class);
    }

    public function approvedComments(): HasMany
    {
        return $this->hasMany(BlogComment::class)->where('status', 'approved')->orderBy('created_at');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Central\PlatformUser::class, 'author_id');
    }

    public function isPublished(): bool
    {
        return $this->status === 'published' && $this->published_at && $this->published_at->isPast();
    }

    /** Falls back to the post title if no custom meta title was set. */
    public function effectiveMetaTitle(): string
    {
        return $this->meta_title ?: $this->title;
    }

    public function featuredImageUrl(): ?string
    {
        if (!$this->featured_image_path) return null;
        return Storage::disk(config('blog.storage_disk'))->url($this->featured_image_path);
    }

    public function canonicalUrl(): string
    {
        return rtrim(config('app.url'), '/') . '/blog/' . $this->slug;
    }

    /** Real JSON-LD Article schema, per-post. */
    public function schemaJsonLd(): array
    {
        return [
            '@context'         => 'https://schema.org',
            '@type'            => 'Article',
            'headline'         => $this->title,
            'description'      => $this->meta_description ?: $this->excerpt,
            'image'            => $this->featuredImageUrl(),
            'datePublished'    => $this->published_at?->toIso8601String(),
            'dateModified'     => $this->updated_at->toIso8601String(),
            'author'           => ['@type' => 'Organization', 'name' => 'Koordli'],
            'mainEntityOfPage' => $this->canonicalUrl(),
        ];
    }
}