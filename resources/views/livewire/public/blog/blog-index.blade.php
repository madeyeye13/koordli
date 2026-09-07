<div>
@include('partials.public-nav')

<style>
    .blog-hero { text-align:center; padding:120px 20px 40px; }
    .blog-hero h1 { font-size:clamp(28px,5vw,42px); font-weight:800; letter-spacing:-0.02em; margin-bottom:10px; color:var(--lp-text); font-family:'Fraunces',serif; }
    .blog-hero p { font-size:15px; color:var(--lp-text-muted); }
    .container { max-width:1100px; margin:0 auto; padding:0 20px; }
    .search-row { display:flex; justify-content:center; margin:28px 0; }
    .search-input { width:320px; max-width:100%; padding:10px 14px; border:1px solid var(--lp-border); border-radius:8px; font-size:13.5px; background:var(--lp-card-bg); color:var(--lp-text); }
    .filter-pills { display:flex; gap:8px; flex-wrap:wrap; justify-content:center; margin-bottom:40px; }
    .pill { font-size:12.5px; padding:6px 14px; border-radius:20px; border:1px solid var(--lp-border); color:var(--lp-text-muted); cursor:pointer; background:var(--lp-card-bg); }
    .pill.active { background:var(--lp-accent); color:#fff; border-color:var(--lp-accent); }
    .blog-grid { display:grid; grid-template-columns:repeat(3, 1fr); gap:24px; padding-bottom:60px; }
    @media (max-width:900px) { .blog-grid { grid-template-columns:repeat(2, 1fr); } }
    @media (max-width:640px) { .blog-grid { grid-template-columns:1fr; } }
    .blog-card { border:1px solid var(--lp-border); border-radius:14px; overflow:hidden; transition:transform 180ms ease, box-shadow 180ms ease; text-decoration:none; display:block; background:var(--lp-card-bg); }
    .blog-card:hover { transform:translateY(-3px); box-shadow:0 16px 32px -16px rgba(28,25,23,0.16); }
    .card-img { width:100%; height:170px; object-fit:cover; background:var(--lp-bg-alt); }
    .card-body { padding:18px; }
    .card-cats { display:flex; gap:6px; margin-bottom:8px; flex-wrap:wrap; }
    .card-cat { font-size:10.5px; font-weight:600; color:var(--lp-accent); background:var(--lp-accent-bg); padding:2px 8px; border-radius:10px; }
    .card-title { font-size:16px; font-weight:700; margin-bottom:6px; line-height:1.35; color:var(--lp-text); }
    .card-excerpt { font-size:13px; color:var(--lp-text-muted); line-height:1.6; }
    .card-date { font-size:11.5px; color:var(--lp-text-faint); margin-top:12px; }
    .empty { text-align:center; padding:60px 20px; color:var(--lp-text-faint); }
    .pagination { padding-bottom:60px; }
</style>

<div class="blog-hero">
    <h1>The Koordli Blog</h1>
    <p>Practical guides for running a better event business.</p>
</div>

<div class="container">
    <div class="search-row">
        <input type="text" wire:model.live.debounce.400ms="q" class="search-input" placeholder="Search articles...">
    </div>

    <div class="filter-pills">
        <span wire:click="$set('category', '')" class="pill {{ !$category ? 'active' : '' }}">All</span>
        @foreach($categories as $cat)
        <span wire:click="$set('category', '{{ $cat->slug }}')" class="pill {{ $category === $cat->slug ? 'active' : '' }}">{{ $cat->name }}</span>
        @endforeach
    </div>

    @if($posts->isEmpty())
    <div class="empty">No articles found.</div>
    @else
    <div class="blog-grid">
        @foreach($posts as $post)
        <a href="{{ route('blog.show', $post->slug) }}" wire:navigate class="blog-card">
            @if($post->featuredImageUrl())
            <img src="{{ $post->featuredImageUrl() }}" class="card-img" loading="lazy" alt="{{ $post->title }}">
            @else
            <div class="card-img"></div>
            @endif
            <div class="card-body">
                @if($post->categories->isNotEmpty())
                <div class="card-cats">
                    @foreach($post->categories as $cat)<span class="card-cat">{{ $cat->name }}</span>@endforeach
                </div>
                @endif
                <div class="card-title">{{ $post->title }}</div>
                <div class="card-excerpt">{{ $post->excerpt }}</div>
                <div class="card-date">{{ $post->published_at->format('M d, Y') }}</div>
            </div>
        </a>
        @endforeach
    </div>
    <div class="pagination">{{ $posts->links() }}</div>
    @endif
</div>
</div>