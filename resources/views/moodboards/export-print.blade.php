{{-- resources/views/moodboards/export-print.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $moodboard->title }}</title>
    <style>
        body { font-family: sans-serif; padding: 30px; color: #1C1917; }
        .item { break-inside: avoid; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid #E7E5E4; }
        img { max-width: 100%; max-height: 400px; }
        .swatch { width: 100px; height: 60px; border-radius: 4px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <button class="no-print" onclick="window.print()" style="margin-bottom:20px;padding:8px 16px;">Print / Save as PDF</button>
    <h1>{{ $moodboard->title }}</h1>
    @if($moodboard->description)<p>{{ $moodboard->description }}</p>@endif

    @foreach($moodboard->items as $item)
    <div class="item">
        @if($item->type === 'image' && $item->document)
            <img src="{{ \Illuminate\Support\Facades\Storage::disk($item->document->disk)->url($item->document->path) }}">
            @if($item->data['caption'] ?? null)<p>{{ $item->data['caption'] }}</p>@endif
        @elseif($item->type === 'note')
            <p>{{ $item->data['content'] ?? '' }}</p>
        @elseif($item->type === 'text')
            <h3>{{ $item->data['heading'] ?? '' }}</h3>
            <p>{{ $item->data['content'] ?? '' }}</p>
        @elseif($item->type === 'color')
            <div class="swatch" style="background:{{ $item->data['value'] ?? '#ccc' }};"></div>
            <p>{{ $item->data['name'] ?? $item->data['value'] ?? '' }} — {{ $item->data['note'] ?? '' }}</p>
        @elseif($item->type === 'link')
            <p><strong>{{ $item->data['title'] ?? '' }}</strong><br>{{ $item->data['url'] ?? '' }}<br>{{ $item->data['description'] ?? '' }}</p>
        @elseif($item->type === 'file' && $item->document)
            <p>📄 {{ $item->data['name'] ?? $item->document->name }}</p>
        @endif
    </div>
    @endforeach
</body>
</html>