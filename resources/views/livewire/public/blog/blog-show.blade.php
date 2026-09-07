<div>
@include('partials.public-nav')

@push('head')
<meta name="description" content="{{ $post->meta_description }}">
<link rel="canonical" href="{{ $post->canonicalUrl() }}">
<meta property="og:type" content="article">
<meta property="og:title" content="{{ $post->effectiveMetaTitle() }}">
<meta property="og:description" content="{{ $post->meta_description }}">
@if($post->featuredImageUrl())<meta property="og:image" content="{{ $post->featuredImageUrl() }}">@endif
<meta property="og:url" content="{{ $post->canonicalUrl() }}">
<script type="application/ld+json">{!! json_encode($post->schemaJsonLd()) !!}</script>
@endpush

<style>
    .blog-layout { max-width:1140px; margin:0 auto; padding:120px 20px 100px; display:grid; grid-template-columns:1fr 300px; gap:48px; align-items:start; }
    @media (max-width:860px) { .blog-layout { grid-template-columns:1fr; } }
    .blog-main .eyebrow { font-size:11.5px; font-weight:700; color:var(--lp-accent); text-transform:uppercase; letter-spacing:0.05em; margin-bottom:10px; }
    .blog-main h1 { font-size:clamp(26px,4vw,38px); font-weight:800; letter-spacing:-0.02em; margin-bottom:14px; line-height:1.2; color:var(--lp-text); font-family:'Fraunces',serif; }
    .blog-meta { font-size:12.5px; color:var(--lp-text-faint); margin-bottom:28px; }
    .featured-img { width:100%; border-radius:14px; margin-bottom:32px; }
    .blog-content { font-size:15.5px; color:var(--lp-text); line-height:1.75; }
    .blog-content h2 { font-size:22px; font-weight:700; margin:32px 0 12px; }
    .blog-content h3 { font-size:18px; font-weight:700; margin:26px 0 10px; }
    .blog-content p { margin-bottom:16px; }
    .blog-content ul, .blog-content ol { margin:0 0 16px 24px; }
    .blog-content li { margin-bottom:6px; }
    .blog-content blockquote { border-left:3px solid var(--lp-accent); padding-left:16px; margin:20px 0; color:var(--lp-text-muted); font-style:italic; }
    .blog-content img { max-width:100%; border-radius:10px; margin:20px 0; }
    .blog-content pre { background:#1C1917; color:#E5E5E5; padding:14px 18px; border-radius:10px; overflow-x:auto; margin:20px 0; }
    .tags-row { display:flex; gap:8px; flex-wrap:wrap; margin-top:36px; padding-top:24px; border-top:1px solid var(--lp-border); }
    .tag-pill { font-size:12px; background:var(--lp-bg-alt); color:var(--lp-text-muted); padding:5px 12px; border-radius:16px; text-decoration:none; }
    .blog-sidebar { position:sticky; top:100px; border-left:1px solid var(--lp-border); padding-left:32px; }
    @media (max-width:860px) { .blog-sidebar { border-left:none; padding-left:0; margin-top:40px; } }
    .cta-card { background:var(--lp-accent-bg); border-radius:14px; padding:22px; margin-bottom:32px; }
    .cta-card h3 { font-size:16px; margin-bottom:8px; color:var(--lp-text); }
    .cta-card p { font-size:13px; color:var(--lp-text-muted); margin-bottom:14px; }
    .cta-btn { display:inline-block; background:var(--lp-accent); color:#fff; font-size:13px; font-weight:600; padding:9px 18px; border-radius:8px; text-decoration:none; }
    .side-label { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; color:var(--lp-text-faint); margin-bottom:14px; }
    .recent-item { display:block; font-size:13.5px; font-weight:500; color:var(--lp-text); margin-bottom:14px; text-decoration:none; }
    .recent-item:hover { color:var(--lp-accent); }
    .sidebar-tags { display:flex; gap:6px; flex-wrap:wrap; margin-top:32px; }
    .sidebar-tag { font-size:11.5px; background:var(--lp-bg-alt); color:var(--lp-text-muted); padding:4px 10px; border-radius:14px; text-decoration:none; }
    .comments-section { margin-top:56px; padding-top:32px; border-top:1px solid var(--lp-border); }
    .comments-section h3 { font-size:18px; margin-bottom:20px; color:var(--lp-text); }
    .comment-form input, .comment-form textarea { width:100%; padding:10px 14px; border:1px solid var(--lp-border); border-radius:8px; font-size:13.5px; margin-bottom:10px; background:var(--lp-card-bg); color:var(--lp-text); }
    .comment-submit { background:var(--lp-text); color:var(--lp-bg); border:none; padding:10px 20px; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; }
    .comment-item { padding:16px 0; border-bottom:1px solid var(--lp-bg-alt); }
    .comment-name { font-size:13px; font-weight:700; color:var(--lp-text); }
    .comment-date { font-size:11px; color:var(--lp-text-faint); margin-left:8px; }
    .comment-body { font-size:13.5px; color:var(--lp-text-muted); margin-top:6px; }
</style>

<div class="blog-layout">
    <div class="blog-main">
        <a href="{{ route('blog.index') }}" wire:navigate style="font-size:13px;color:var(--lp-text-faint);">← Back to Blog</a>
        @if($post->categories->isNotEmpty())<div class="eyebrow" style="margin-top:16px;">{{ $post->categories->first()->name }}</div>@endif
        <h1>{{ $post->title }}</h1>
        <div class="blog-meta">{{ $post->published_at->format('F d, Y') }}</div>

        @if($post->featuredImageUrl())<img src="{{ $post->featuredImageUrl() }}" class="featured-img" alt="{{ $post->title }}">@endif

        <div class="blog-content">{!! $post->content !!}</div>

        @if($post->tags->isNotEmpty())
        <div class="tags-row">
            @foreach($post->tags as $tag)<a href="{{ route('blog.index', ['tag' => $tag->slug]) }}" wire:navigate class="tag-pill">#{{ $tag->name }}</a>@endforeach
        </div>
        @endif

        @if($post->comments_enabled)
        <div class="comments-section">
            <h3>Comments ({{ $post->approvedComments->count() }})</h3>

            @foreach($post->approvedComments as $comment)
            <div class="comment-item">
                <span class="comment-name">{{ $comment->author_name }}</span>
                <span class="comment-date">{{ $comment->created_at->format('M d, Y') }}</span>
                <div class="comment-body">{{ $comment->body }}</div>
            </div>
            @endforeach

            <div class="comment-form" style="margin-top:24px;">
                @if(!$commentSubmitted)
                <input wire:model="commentName" type="text" placeholder="Your name">
                @error('commentName')<span style="color:#DC2626;font-size:11px;">{{ $message }}</span>@enderror
                <input wire:model="commentEmail" type="email" placeholder="Your email (not published)">
                @error('commentEmail')<span style="color:#DC2626;font-size:11px;">{{ $message }}</span>@enderror
                <textarea wire:model="commentBody" rows="4" placeholder="Write a comment..."></textarea>
                @error('commentBody')<span style="color:#DC2626;font-size:11px;">{{ $message }}</span>@enderror
                <button wire:click="submitComment" wire:loading.attr="disabled" class="comment-submit">
                    <span wire:loading.remove wire:target="submitComment">Post Comment</span>
                    <span wire:loading wire:target="submitComment">Sending...</span>
                </button>
                @else
                <p style="font-size:13.5px;color:#059669;">Thanks — your comment is awaiting approval and will appear once reviewed.</p>
                @endif
            </div>
        </div>
        @endif
    </div>

    <div class="blog-sidebar">
        <div class="cta-card">
            <h3>Run your events without the chaos</h3>
            <p>Try Koordli free for 30 days — no card required.</p>
            <a href="{{ route('register') }}" wire:navigate class="cta-btn">Start Free Trial →</a>
        </div>

        @if($recentPosts->isNotEmpty())
        <div style="margin-bottom:32px;">
            <div class="side-label">Recent Posts</div>
            @foreach($recentPosts as $rp)<a href="{{ route('blog.show', $rp->slug) }}" wire:navigate class="recent-item">{{ $rp->title }}</a>@endforeach
        </div>
        @endif

        @if($post->categories->isNotEmpty() || $post->tags->isNotEmpty())
        <div class="side-label">Categories &amp; Tags</div>
        <div class="sidebar-tags">
            @foreach($post->categories as $cat)<a href="{{ route('blog.index', ['category' => $cat->slug]) }}" wire:navigate class="sidebar-tag">{{ $cat->name }}</a>@endforeach
            @foreach($post->tags as $tag)<a href="{{ route('blog.index', ['tag' => $tag->slug]) }}" wire:navigate class="sidebar-tag">#{{ $tag->name }}</a>@endforeach
        </div>
        @endif
    </div>
</div>
</div>