<div x-data="{ showDeleteModal: false, deleteId: null }">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
        <div>
            <div class="krd-label" style="margin-bottom:4px;">Platform</div>
            <h2 class="krd-heading-3" style="color:#1C1917;">Blog</h2>
        </div>
        <a href="{{ route('platform.blog.create') }}" wire:navigate class="krd-btn krd-btn-primary">+ New Post</a>
    </div>

    <div style="display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap;">
        <input wire:model.live.debounce.300ms="search" type="text" class="krd-input" placeholder="Search posts..." style="max-width:260px;">

        <div x-data="{ open:false }" x-on:click.outside="open=false" style="position:relative;min-width:150px;">
            <button type="button" x-on:click="open=!open" x-bind:class="open?'krd-dropdown-trigger open':'krd-dropdown-trigger'" style="width:100%;">
                <span x-text="{ '': 'All statuses', 'published': 'Published', 'draft': 'Draft' }[$wire.statusFilter]"></span>
                <svg class="krd-dropdown-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
            </button>
            <div x-show="open" x-cloak class="krd-dropdown-menu">
                <div class="krd-dropdown-option" x-on:click="$wire.set('statusFilter',''); open=false">All statuses</div>
                <div class="krd-dropdown-option" x-on:click="$wire.set('statusFilter','published'); open=false">Published</div>
                <div class="krd-dropdown-option" x-on:click="$wire.set('statusFilter','draft'); open=false">Draft</div>
            </div>
        </div>
    </div>

    <div class="krd-card platform-blog-table-card" style="padding:0;overflow:hidden;">
        <div class="platform-blog-table-wrap platform-blog-desktop-list">
        <table class="krd-table">
            <thead><tr><th>Title</th><th>Categories</th><th>Status</th><th>Published</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($posts as $post)
                <tr>
                    <td style="font-weight:500;color:#1C1917;">{{ $post->title }}</td>
                    <td style="font-size:12px;color:#78716C;">{{ $post->categories->pluck('name')->join(', ') ?: '—' }}</td>
                    <td><span class="krd-badge {{ $post->status === 'published' ? 'krd-badge-green' : 'krd-badge-stone' }}">{{ ucfirst($post->status) }}</span></td>
                    <td style="font-size:12px;color:#78716C;">{{ $post->published_at?->format('M d, Y') ?? '—' }}</td>
                    <td>
                        <div style="display:flex;gap:6px;flex-wrap:wrap;">
                            <a href="{{ route('platform.blog.edit', $post->id) }}" wire:navigate class="krd-btn krd-btn-secondary krd-btn-sm">Edit</a>
                            <button wire:click="togglePublish({{ $post->id }})" class="krd-btn krd-btn-sm" style="background:{{ $post->status === 'published' ? '#FEE2E2' : '#D1FAE5' }};color:{{ $post->status === 'published' ? '#DC2626' : '#059669' }};">
                                {{ $post->status === 'published' ? 'Unpublish' : 'Publish' }}
                            </button>
                            <button type="button" x-on:click="showDeleteModal = true; deleteId = {{ $post->id }}" class="krd-btn krd-btn-sm" style="background:#FEE2E2;color:#DC2626;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5"><div class="krd-empty-state"><div class="krd-empty-state-icon">✍️</div><div class="krd-empty-state-title">No posts yet</div></div></td></tr>
                @endforelse
            </tbody>
        </table>
        </div>

        <div class="platform-blog-mobile-list">
            @forelse($posts as $post)
            <div style="padding:14px 16px;border-bottom:1px solid #E7E5E4;">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;margin-bottom:8px;">
                    <div style="font-size:13px;font-weight:600;color:#1C1917;line-height:1.4;min-width:0;">{{ $post->title }}</div>
                    <span class="krd-badge {{ $post->status === 'published' ? 'krd-badge-green' : 'krd-badge-stone' }}" style="flex-shrink:0;">
                        {{ ucfirst($post->status) }}
                    </span>
                </div>
                <div style="font-size:12px;color:#78716C;line-height:1.5;margin-bottom:10px;">
                    {{ $post->categories->pluck('name')->join(', ') ?: 'Uncategorized' }}
                    <span style="color:#A8A29E;"> · {{ $post->published_at?->format('M d, Y') ?? 'Not published' }}</span>
                </div>
                <div style="display:flex;gap:6px;flex-wrap:wrap;">
                    <a href="{{ route('platform.blog.edit', $post->id) }}" wire:navigate class="krd-btn krd-btn-secondary krd-btn-sm">Edit</a>
                    <button wire:click="togglePublish({{ $post->id }})" class="krd-btn krd-btn-sm" style="background:{{ $post->status === 'published' ? '#FEE2E2' : '#D1FAE5' }};color:{{ $post->status === 'published' ? '#DC2626' : '#059669' }};">
                        {{ $post->status === 'published' ? 'Unpublish' : 'Publish' }}
                    </button>
                    <button type="button" x-on:click="showDeleteModal = true; deleteId = {{ $post->id }}" class="krd-btn krd-btn-sm" style="background:#FEE2E2;color:#DC2626;">
                        Delete
                    </button>
                </div>
            </div>
            @empty
            <div class="krd-empty-state"><div class="krd-empty-state-icon">✍️</div><div class="krd-empty-state-title">No posts yet</div></div>
            @endforelse
        </div>

        @if($posts->hasPages())<div style="padding:12px 16px;border-top:1px solid #E7E5E4;">{{ $posts->links() }}</div>@endif
    </div>

    <template x-teleport="body">
    <div x-show="showDeleteModal" x-cloak
        style="position:fixed;left:0;top:0;width:100vw;height:100vh;background:rgba(0,0,0,0.4);z-index:60;"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">
        <div style="position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);background:#fff;border-radius:8px;padding:28px;max-width:400px;width:90%;box-shadow:0 20px 50px rgba(28,25,23,0.2);"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">
            <h3 style="font-size:16px;font-weight:600;color:#1C1917;margin-bottom:8px;">Delete Post?</h3>
            <p style="font-size:13px;color:#78716C;margin-bottom:24px;line-height:1.6;">
                This will permanently delete this blog post and cannot be undone.
            </p>
            <div style="display:flex;gap:10px;">
                <button type="button" x-on:click="showDeleteModal = false; $wire.delete(deleteId); deleteId = null;" class="krd-btn krd-btn-danger" style="flex:1;">Yes, Delete</button>
                <button type="button" x-on:click="showDeleteModal = false; deleteId = null" class="krd-btn krd-btn-secondary" style="flex:1;">Cancel</button>
            </div>
        </div>
    </div>
    </template>
</div>