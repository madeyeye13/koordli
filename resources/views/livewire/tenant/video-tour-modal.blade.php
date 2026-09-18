<div x-data="{ videoOpen: false }">
    @if($hasVideo)
        @if($showPrompt)
            <div class="fixed inset-0 z-[70] flex items-center justify-center bg-black/55 px-4">
                <div style="width:100%;max-width:420px;border-radius:16px;background:#fff;padding:40px 32px;box-shadow:0 20px 60px rgba(0,0,0,0.25);">
                    <div style="text-align:center;">
                        
                        <h2 style="font-size:22px;font-weight:700;color:#1C1917;margin-bottom:10px;">Welcome to Koordli!</h2>
                        <p style="font-size:14px;color:#78716C;line-height:1.6;">Want a quick tour of your new workspace?</p>
                    </div>

                    <div style="margin-top:28px;display:flex;align-items:center;justify-content:center;gap:12px;">
                        <button
                            type="button"
                            wire:click="open"
                            x-on:click="videoOpen = true"
                            style="background:var(--tenant-primary);color:#fff;border:none;border-radius:10px;padding:11px 22px;font-size:14px;font-weight:600;cursor:pointer;transition:opacity 150ms;"
                            onmouseover="this.style.opacity=0.9" onmouseout="this.style.opacity=1"
                        >
                            Watch Tour
                        </button>

                        <button
                            type="button"
                            wire:click="skipPrompt"
                            style="background:none;border:none;color:#78716C;font-size:14px;font-weight:500;cursor:pointer;padding:11px 12px;"
                            onmouseover="this.style.color='#57534E'" onmouseout="this.style.color='#78716C'"
                        >
                            Skip
                        </button>
                    </div>
                </div>
            </div>
        @endif

        @if($showButton)
            <button
                type="button"
                wire:click="open"
                x-on:click="videoOpen = true"
                class="tenant-tour-button"
                                title="Watch a quick tour"
                style="background:#F5F3FF;border:1px solid #DDD6FE;border-radius:8px;cursor:pointer;color:var(--tenant-primary);display:flex;align-items:center;gap:6px;padding:6px 12px;font-size:13px;font-weight:600;"
                aria-label="Watch a quick tour"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M10 8.5v7l6-3.5-6-3.5z" fill="currentColor" stroke="none"/>
                </svg>
                <span>Watch Tour</span>
            </button>
        @endif

        <div
                x-cloak
                x-show="videoOpen || $wire.isOpen"
                wire:click.self="close"
                x-on:click.self="videoOpen = false"
                class="fixed inset-0 z-[60] flex items-center justify-center bg-black/70 px-4"
            >
                <div class="relative w-full max-w-4xl overflow-hidden rounded-2xl border border-stone-800 bg-stone-950 shadow-2xl">
                    <button
                        type="button"
                        wire:click="close"
                        x-on:click="videoOpen = false"
                        class="absolute right-3 top-3 z-10 flex h-9 w-9 items-center justify-center rounded-full border border-stone-700 bg-stone-900/80 text-lg text-stone-100 transition hover:bg-stone-800"
                        aria-label="Close tour video"
                    >
                        ×
                    </button>

                    <div class="bg-stone-950 p-3 pb-0">
                        <div class="overflow-hidden rounded-xl bg-black">
                            <video
                                controls
                                playsinline
                                class="aspect-video w-full"
                                src="{{ Storage::url($videoUrl) }}"
                                @ended="$wire.markSeen()"
                            ></video>
                        </div>
                    </div>

                </div>
            </div>
    @endif
</div>

