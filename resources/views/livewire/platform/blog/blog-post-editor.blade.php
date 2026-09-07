<div x-data="{
    editorApi: null,
    uploadingImage: false,
    showCtaModal: false,
    ctaHref: '', ctaLabel: '',
    async uploadImageFile(file) {
        const fd = new FormData();
        fd.append('image', file);
        const res = await fetch('{{ route('platform.blog.upload-image') }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
            body: fd,
        });
        const data = await res.json();
        return data.url;
    },
    init() {
        this.editorApi = window.initBlogEditor(
            'tiptap-editor',
            @js($content),
            (html) => { $wire.updateContent(html); },
            (file) => this.uploadImageFile(file),
            () => { this.uploadingImage = true; },
            () => { this.uploadingImage = false; }
        );

        document.getElementById('blog-image-input').addEventListener('change', async (e) => {
            const file = e.target.files[0];
            if (!file) return;
            this.uploadingImage = true;
            const url = await this.uploadImageFile(file);
            this.editorApi.insertImage(url);
            this.uploadingImage = false;
            e.target.value = '';
        });
    },
    insertCta() {
        if (!this.ctaHref || !this.ctaLabel) return;
        this.editorApi.insertCta(this.ctaHref, this.ctaLabel);
        this.showCtaModal = false;
        this.ctaHref = ''; this.ctaLabel = '';
    }
}">
    <div style="margin-bottom:20px;">
        <a href="{{ route('platform.blog.index') }}" wire:navigate style="color:#A8A29E;font-size:13px;">← Back to Blog</a>
        <h2 class="krd-heading-3" style="margin-top:6px;">{{ $post ? 'Edit Post' : 'New Post' }}</h2>
    </div>

    <div style="display:grid;grid-template-columns:1fr 320px;gap:24px;align-items:start;">
        <div style="display:flex;flex-direction:column;gap:16px;">

            <div class="krd-card" style="padding:24px;">
                <input wire:model="title" type="text" class="krd-input" placeholder="Post title" style="font-size:20px;font-weight:700;border:none;padding-left:0;margin-bottom:16px;">
                <textarea wire:model="excerpt" class="krd-input" rows="2" placeholder="Short excerpt (used in previews and as a fallback meta description)"></textarea>
            </div>

            {{-- wire:ignore (NOT .self) — this protects the entire
                 subtree, including everything TipTap mounts inside it,
                 from ever being touched by Livewire's re-render again.
                 .self only protects this element itself, not its
                 children — which is exactly why the previous attempt
                 using .self didn't actually fix the wipe-on-re-render
                 problem. --}}
            <div class="krd-card" style="padding:0;overflow:hidden;" wire:ignore>
                {{-- Toolbar --}}
                <div style="display:flex;gap:4px;padding:10px;border-bottom:1px solid #E7E5E4;flex-wrap:wrap;align-items:center;" x-data="{ showColorMenu:false, showHighlightMenu:false, showImageMenu:false, showHeadingMenu:false }">

                    {{-- Headings 1-6 --}}
                    <div style="position:relative;">
                        <button type="button" x-on:click="showHeadingMenu = !showHeadingMenu" class="krd-btn krd-btn-ghost krd-btn-sm">Heading ▾</button>
                        <div x-show="showHeadingMenu" x-on:click.outside="showHeadingMenu=false" x-cloak class="krd-dropdown-menu" style="position:absolute;top:100%;left:0;width:120px;">
                            <template x-for="level in [1,2,3,4,5,6]" :key="level">
                                <div class="krd-dropdown-option" x-on:click="editorApi.toggleHeading(level); showHeadingMenu=false" x-text="'Heading ' + level"></div>
                            </template>
                            <div class="krd-dropdown-option" x-on:click="editorApi.editor.chain().focus().setParagraph().run(); showHeadingMenu=false">Paragraph</div>
                        </div>
                    </div>

                    <span style="width:1px;height:20px;background:#E7E5E4;margin:0 4px;"></span>

                    {{-- Basic styling --}}
                    <button type="button" x-on:click="editorApi.toggleBold()" class="krd-btn krd-btn-ghost krd-btn-sm" style="font-weight:700;">B</button>
                    <button type="button" x-on:click="editorApi.toggleItalic()" class="krd-btn krd-btn-ghost krd-btn-sm" style="font-style:italic;">I</button>
                    <button type="button" x-on:click="editorApi.toggleUnderline()" class="krd-btn krd-btn-ghost krd-btn-sm" style="text-decoration:underline;">U</button>
                    <button type="button" x-on:click="editorApi.toggleStrike()" class="krd-btn krd-btn-ghost krd-btn-sm" style="text-decoration:line-through;">S</button>

                    {{-- Text color --}}
                    <div style="position:relative;">
                        <button type="button" x-on:click="showColorMenu = !showColorMenu" class="krd-btn krd-btn-ghost krd-btn-sm">🎨</button>
                        <div x-show="showColorMenu" x-on:click.outside="showColorMenu=false" x-cloak style="position:absolute;top:100%;left:0;background:#fff;border:1px solid #E7E5E4;border-radius:8px;padding:8px;display:flex;gap:6px;z-index:10;">
                            <template x-for="c in ['#1C1917','#7C3AED','#EF4444','#10B981','#F59E0B','#3B82F6']" :key="c">
                                <button type="button" x-on:click="editorApi.setColor(c); showColorMenu=false" :style="`width:20px;height:20px;border-radius:50%;background:${c};border:1px solid #E7E5E4;cursor:pointer;`"></button>
                            </template>
                        </div>
                    </div>

                    {{-- Highlight --}}
                    <div style="position:relative;">
                        <button type="button" x-on:click="showHighlightMenu = !showHighlightMenu" class="krd-btn krd-btn-ghost krd-btn-sm">🖍️</button>
                        <div x-show="showHighlightMenu" x-on:click.outside="showHighlightMenu=false" x-cloak style="position:absolute;top:100%;left:0;background:#fff;border:1px solid #E7E5E4;border-radius:8px;padding:8px;display:flex;gap:6px;z-index:10;">
                            <template x-for="c in ['#FEF3C7','#DBEAFE','#D1FAE5','#FEE2E2','#F5F3FF']" :key="c">
                                <button type="button" x-on:click="editorApi.setHighlight(c); showHighlightMenu=false" :style="`width:20px;height:20px;border-radius:50%;background:${c};border:1px solid #E7E5E4;cursor:pointer;`"></button>
                            </template>
                        </div>
                    </div>

                    <span style="width:1px;height:20px;background:#E7E5E4;margin:0 4px;"></span>

                    {{-- Structure --}}
                    <button type="button" x-on:click="editorApi.toggleBlockquote()" class="krd-btn krd-btn-ghost krd-btn-sm">" Quote</button>
                    <button type="button" x-on:click="editorApi.toggleCodeBlock()" class="krd-btn krd-btn-ghost krd-btn-sm" style="font-family:monospace;">{ }</button>
                    <button type="button" x-on:click="editorApi.setHorizontalRule()" class="krd-btn krd-btn-ghost krd-btn-sm">─ Rule</button>

                    <span style="width:1px;height:20px;background:#E7E5E4;margin:0 4px;"></span>

                    {{-- Lists --}}
                    <button type="button" x-on:click="editorApi.toggleBulletList()" class="krd-btn krd-btn-ghost krd-btn-sm">• List</button>
                    <button type="button" x-on:click="editorApi.toggleOrderedList()" class="krd-btn krd-btn-ghost krd-btn-sm">1. List</button>
                    <button type="button" x-on:click="editorApi.toggleTaskList()" class="krd-btn krd-btn-ghost krd-btn-sm">☑ Checklist</button>

                    <span style="width:1px;height:20px;background:#E7E5E4;margin:0 4px;"></span>

                    {{-- Media --}}
                    <button type="button" x-on:click="const u=prompt('Link URL'); if(u) editorApi.setLink(u)" class="krd-btn krd-btn-ghost krd-btn-sm">🔗 Link</button>
                    <button type="button" x-on:click="document.getElementById('blog-image-input').click()" class="krd-btn krd-btn-ghost krd-btn-sm">🖼️ Image</button>
                    <input type="file" id="blog-image-input" accept="image/*" style="display:none;">
                    <button type="button" x-on:click="const u=prompt('YouTube or Vimeo URL'); if(u) editorApi.insertYoutube(u)" class="krd-btn krd-btn-ghost krd-btn-sm">▶️ Video</button>

                    <button type="button" x-on:click="showCtaModal = true" class="krd-btn krd-btn-sm" style="background:#7C3AED;color:#fff;margin-left:auto;">+ CTA Button</button>
                </div>
                <div style="position:relative;">
                    <div id="tiptap-editor" style="padding:20px;min-height:400px;font-size:14px;line-height:1.7;"></div>
                    <div x-show="uploadingImage" x-cloak style="position:absolute;top:12px;right:12px;background:#1C1917;color:#fff;font-size:12px;padding:6px 12px;border-radius:20px;display:flex;align-items:center;gap:8px;">
                        <span style="display:inline-block;width:12px;height:12px;border:2px solid #fff;border-top-color:transparent;border-radius:50%;animation:krd-spin 0.6s linear infinite;"></span>
                        Uploading image...
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div style="display:flex;flex-direction:column;gap:16px;position:sticky;top:80px;">
            <div class="krd-card" style="padding:20px;">
                <button wire:click="save('publish')" wire:loading.attr="disabled" class="krd-btn krd-btn-primary" style="width:100%;margin-bottom:8px;">
                    <span wire:loading.remove wire:target="save">{{ $status === 'published' ? 'Update & Publish' : 'Publish' }}</span>
                    <span wire:loading wire:target="save">Saving...</span>
                </button>
                <button wire:click="save('draft')" wire:loading.attr="disabled" class="krd-btn krd-btn-secondary" style="width:100%;">Save Draft</button>
            </div>

            <div class="krd-card" style="padding:20px;">
                <div class="krd-label" style="margin-bottom:10px;">Featured Image</div>
                @if($featured_image_url)<img src="{{ $featured_image_url }}" style="width:100%;border-radius:8px;margin-bottom:10px;">@endif
                <input wire:model="featured_image" type="file" accept="image/*" class="krd-input" style="font-size:12px;">
                @if($featured_image)
                <button wire:click="uploadFeaturedImage" wire:loading.attr="disabled" class="krd-btn krd-btn-secondary krd-btn-sm" style="width:100%;margin-top:8px;">
                    <span wire:loading.remove wire:target="uploadFeaturedImage">Upload</span>
                    <span wire:loading wire:target="uploadFeaturedImage">Uploading...</span>
                </button>
                @endif
            </div>

            <div class="krd-card" style="padding:20px;">
                <div class="krd-label" style="margin-bottom:10px;">Categories</div>
                <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:10px;" x-data="{ selected: @js($selectedCategoryIds) }">
                    @foreach($categories as $cat)
                    <span x-on:click="
                            selected.includes({{ $cat->id }}) ? selected.splice(selected.indexOf({{ $cat->id }}),1) : selected.push({{ $cat->id }});
                            $wire.toggleCategory({{ $cat->id }});
                        "
                        class="krd-badge" style="cursor:pointer;"
                        x-bind:style="selected.includes({{ $cat->id }}) ? 'cursor:pointer;background:#7C3AED;color:#fff;' : 'cursor:pointer;background:#F5F5F4;color:#57534E;'">{{ $cat->name }}</span>
                    @endforeach
                </div>
                <div style="display:flex;gap:6px;">
                    <input wire:model="newCategoryName" wire:keydown.enter="createCategory" type="text" class="krd-input" placeholder="New category" style="font-size:12px;">
                    <button wire:click="createCategory" class="krd-btn krd-btn-secondary krd-btn-sm">+</button>
                </div>
            </div>

            <div class="krd-card" style="padding:20px;">
                <div class="krd-label" style="margin-bottom:10px;">Tags</div>
                <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:10px;" x-data="{ selected: @js($selectedTagIds) }">
                    @foreach($tags as $tag)
                    <span x-on:click="
                            selected.includes({{ $tag->id }}) ? selected.splice(selected.indexOf({{ $tag->id }}),1) : selected.push({{ $tag->id }});
                            $wire.toggleTag({{ $tag->id }});
                        "
                        class="krd-badge" style="cursor:pointer;"
                        x-bind:style="selected.includes({{ $tag->id }}) ? 'cursor:pointer;background:#7C3AED;color:#fff;' : 'cursor:pointer;background:#F5F5F4;color:#57534E;'">{{ $tag->name }}</span>
                    @endforeach
                </div>
                <div style="display:flex;gap:6px;">
                    <input wire:model="newTagName" wire:keydown.enter="createTag" type="text" class="krd-input" placeholder="New tag" style="font-size:12px;">
                    <button wire:click="createTag" class="krd-btn krd-btn-secondary krd-btn-sm">+</button>
                </div>
            </div>

            <div class="krd-card" style="padding:20px;">
                <div class="krd-label" style="margin-bottom:10px;">SEO</div>
                <label class="krd-label-text">Meta Title</label>
                <input type="text" class="krd-input" value="{{ $title ?: 'Auto from title' }}" disabled style="margin-bottom:10px;opacity:0.6;">
                <label class="krd-label-text">Meta Description</label>
                <textarea wire:model="meta_description" class="krd-input" rows="3" maxlength="300"></textarea>
            </div>

            <div class="krd-card" style="padding:20px;">
                <label style="display:flex;align-items:center;gap:8px;font-size:13px;">
                    <input wire:model="comments_enabled" type="checkbox" style="accent-color:#7C3AED;">
                    Allow comments on this post
                </label>
            </div>
        </div>
    </div>

    {{-- CTA Modal --}}
    <template x-teleport="body">
    <div x-show="showCtaModal" x-cloak style="position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:60;display:flex;align-items:center;justify-content:center;padding:16px;">
        <div style="background:#fff;border-radius:8px;padding:24px;max-width:380px;width:100%;">
            <h3 style="font-size:15px;font-weight:600;margin-bottom:12px;">Insert CTA Button</h3>
            <input x-model="ctaLabel" type="text" class="krd-input" placeholder="Button label, e.g. Start Free Trial" style="margin-bottom:8px;">
            <input x-model="ctaHref" type="text" class="krd-input" placeholder="Link URL">
            <div style="display:flex;gap:10px;margin-top:16px;">
                <button type="button" x-on:click="insertCta()" class="krd-btn krd-btn-primary" style="flex:1;">Insert</button>
                <button type="button" x-on:click="showCtaModal=false" class="krd-btn krd-btn-secondary" style="flex:1;">Cancel</button>
            </div>
        </div>
    </div>
    </template>
</div>