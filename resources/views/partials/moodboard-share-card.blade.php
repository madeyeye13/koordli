{{-- resources/views/partials/moodboard-share-card.blade.php --}}
@if($moodboard)
<a href="{{ route('tenant.moodboards.edit', $moodboard->id) }}" style="display:block;text-decoration:none;background:#fff;border:1px solid #E7E5E4;border-radius:10px;overflow:hidden;max-width:260px;margin-top:6px;{{ $alignRight ?? false ? 'margin-left:auto;' : '' }}">
    <div style="width:100%;height:100px;background:#F5F5F4;display:flex;align-items:center;justify-content:center;overflow:hidden;">
        @if($moodboard->coverDocument)
            <img src="{{ \Illuminate\Support\Facades\Storage::disk($moodboard->coverDocument->disk)->url($moodboard->coverDocument->path) }}" style="width:100%;height:100%;object-fit:cover;">
        @else
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="#D6D3D1" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
        @endif
    </div>
    <div style="padding:8px 10px;">
        <div style="font-size:10px;color:#A8A29E;text-transform:uppercase;">Moodboard</div>
        <div style="font-size:12.5px;font-weight:600;color:#1C1917;">{{ $moodboard->title }}</div>
    </div>
</a>
@endif