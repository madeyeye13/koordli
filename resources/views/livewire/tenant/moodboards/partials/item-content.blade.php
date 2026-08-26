@if($item->type === 'note')
    <div x-data="{ val: @js($item->data['content'] ?? '') }" style="width:100%;height:100%;background:#FEF3C7;border-radius:4px;padding:8px;">
        <textarea x-model="val" x-on:blur="$wire.updateNoteContent({{ $item->id }}, val)"
            placeholder="Start typing..." style="width:100%;height:100%;background:transparent;border:none;outline:none;resize:none;font-size:12px;color:#78350F;font-family:inherit;"></textarea>
    </div>
@elseif($item->type === 'empty')
    <div x-data="{ menuOpen: false }" style="position:relative;width:100%;height:100%;">
        <button type="button" x-on:click="menuOpen = !menuOpen" style="width:100%;height:100%;background:#FAFAF9;border:2px dashed #D6D3D1;border-radius:6px;display:flex;align-items:center;justify-content:center;cursor:pointer;">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="#A8A29E" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        </button>
        {{-- Teleported to <body> so it can never be clipped by the item
             card's overflow:hidden, regardless of how small the box is. --}}
        <template x-teleport="body">
        <div x-show="menuOpen" x-cloak x-on:click.outside="menuOpen = false"
            style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:#fff;border:1px solid #E7E5E4;border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,0.15);z-index:80;width:180px;padding:4px;">
            <div wire:click="openSlotFill({{ $item->id }}, 'text')" x-on:click="menuOpen = false" style="padding:9px 14px;font-size:12.5px;cursor:pointer;border-radius:5px;">Text</div>
            <div wire:click="openSlotFill({{ $item->id }}, 'color')" x-on:click="menuOpen = false" style="padding:9px 14px;font-size:12.5px;cursor:pointer;border-radius:5px;">Color</div>
            <div wire:click="openSlotFill({{ $item->id }}, 'link')" x-on:click="menuOpen = false" style="padding:9px 14px;font-size:12.5px;cursor:pointer;border-radius:5px;">Link</div>
            <div x-on:click="menuOpen = false; window.MoodboardSlotUpload.open({{ $item->id }})" style="padding:9px 14px;font-size:12.5px;cursor:pointer;border-radius:5px;">Image / File</div>
        </div>
        </template>
    </div>
@elseif($item->type === 'image' && $item->document)
    <img src="{{ \Illuminate\Support\Facades\Storage::disk($item->document->disk)->url($item->document->path) }}" style="width:100%;height:100%;object-fit:cover;border-radius:4px;">
@elseif($item->type === 'file' && $item->document)
    <a href="{{ \Illuminate\Support\Facades\Storage::disk($item->document->disk)->url($item->document->path) }}" target="_blank" style="font-size:12px;color:#7C3AED;">📄 {{ $item->data['name'] ?? $item->document->name }}</a>
@elseif($item->type === 'text')
    <div style="font-size:12px;font-weight:600;color:#1C1917;margin-bottom:4px;">{{ $item->data['heading'] ?? '' }}</div>
    <div style="font-size:11px;color:#78716C;">{{ $item->data['content'] ?? '' }}</div>
@elseif($item->type === 'color')
    <div x-data="{
            editingName: false,
            nameVal: @js($item->data['name'] ?? ''),
            colorVal: @js($item->data['value'] ?? '#CCCCCC'),
            saveName() {
                this.editingName = false;
                $wire.updateColorItem({{ $item->id }}, this.colorVal, this.nameVal);
            },
            saveColor(newColor) {
                this.colorVal = newColor;
                $wire.updateColorItem({{ $item->id }}, this.colorVal, this.nameVal);
            }
         }"
         style="width:100%;height:100%;display:flex;flex-direction:column;">
        <div x-on:dblclick="$refs.colorInput.click()"
             x-bind:style="`background:${colorVal};flex:1;border-radius:4px 4px 0 0;cursor:pointer;display:flex;align-items:flex-start;justify-content:flex-start;padding:6px;`">
            <span style="font-size:10px;font-weight:600;color:rgba(255,255,255,0.9);text-shadow:0 1px 2px rgba(0,0,0,0.3);" x-text="colorVal.toUpperCase()"></span>
            <input x-ref="colorInput" type="color" x-model="colorVal" x-on:change="saveColor(colorVal)" style="position:absolute;width:0;height:0;opacity:0;pointer-events:none;">
        </div>
        <div style="padding:6px;background:#fff;border-radius:0 0 4px 4px;">
            <template x-if="!editingName">
                <div x-on:click="editingName = true" style="font-size:11px;color:#1C1917;cursor:text;min-height:16px;">
                    <span x-text="nameVal || 'Click to name'" x-bind:style="nameVal ? '' : 'color:#A8A29E;'"></span>
                </div>
            </template>
            <template x-if="editingName">
                <input type="text" x-model="nameVal" x-on:blur="saveName()" x-on:keydown.enter="saveName()" x-init="$nextTick(() => $el.focus())"
                    style="font-size:11px;color:#1C1917;border:1px solid #DDD6FE;border-radius:4px;padding:2px 6px;width:100%;outline:none;">
            </template>
        </div>
    </div>
@elseif($item->type === 'link')
    <a href="{{ $item->data['url'] ?? '#' }}" target="_blank" style="font-size:12px;font-weight:600;color:#7C3AED;text-decoration:none;">{{ $item->data['title'] ?? $item->data['url'] ?? '' }}</a>
    <div style="font-size:10px;color:#A8A29E;margin-top:2px;">{{ $item->data['description'] ?? '' }}</div>
@endif