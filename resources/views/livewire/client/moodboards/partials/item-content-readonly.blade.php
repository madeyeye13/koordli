{{-- resources/views/client/moodboards/partials/item-content-readonly.blade.php --}}
@if($item->type === 'note')
    <div style="width:100%;min-height:100px;background:#FEF3C7;border-radius:4px;padding:8px;">
        <p style="font-size:12px;color:#78350F;white-space:pre-wrap;margin:0;">{{ $item->data['content'] ?? '' }}</p>
    </div>
@elseif($item->type === 'empty')
    {{-- A client should never actually encounter an unfilled slot on a
         board that was deliberately shared with them — but rendering
         nothing rather than a clickable "add content" control avoids
         ever exposing an editing affordance on this read-only view. --}}
@elseif($item->type === 'image' && $item->document)
    <img src="{{ \Illuminate\Support\Facades\Storage::disk($item->document->disk)->url($item->document->path) }}" style="width:100%;height:auto;border-radius:4px;display:block;">
@elseif($item->type === 'file' && $item->document)
    <a href="{{ \Illuminate\Support\Facades\Storage::disk($item->document->disk)->url($item->document->path) }}" target="_blank" style="font-size:12px;color:#7C3AED;text-decoration:none;">📄 {{ $item->data['name'] ?? $item->document->name }}</a>
@elseif($item->type === 'text')
    <div style="font-size:13px;font-weight:600;color:#1C1917;margin-bottom:4px;">{{ $item->data['heading'] ?? '' }}</div>
    <div style="font-size:12px;color:#78716C;white-space:pre-wrap;">{{ $item->data['content'] ?? '' }}</div>
@elseif($item->type === 'color')
    <div style="width:100%;height:60px;border-radius:4px 4px 0 0;background:{{ $item->data['value'] ?? '#CCCCCC' }};display:flex;align-items:flex-start;padding:6px;">
        <span style="font-size:10px;font-weight:600;color:rgba(255,255,255,0.9);text-shadow:0 1px 2px rgba(0,0,0,0.3);">{{ strtoupper($item->data['value'] ?? '') }}</span>
    </div>
    <div style="padding:6px;background:#fff;border-radius:0 0 4px 4px;">
        <span style="font-size:11px;color:#1C1917;">{{ $item->data['name'] ?? '' }}</span>
        @if($item->data['note'] ?? null)<div style="font-size:10px;color:#A8A29E;margin-top:2px;">{{ $item->data['note'] }}</div>@endif
    </div>
@elseif($item->type === 'link')
    <a href="{{ $item->data['url'] ?? '#' }}" target="_blank" style="font-size:12px;font-weight:600;color:#7C3AED;text-decoration:none;">{{ $item->data['title'] ?? $item->data['url'] ?? '' }}</a>
    <div style="font-size:11px;color:#A8A29E;margin-top:2px;">{{ $item->data['description'] ?? '' }}</div>
@endif