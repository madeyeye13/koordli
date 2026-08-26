{{-- resources/views/components/ui/dismissible-tip.blade.php --}}
@props(['id', 'text'])
<div x-data="{ show: !localStorage.getItem('krd-tip-dismissed-{{ $id }}') }" x-show="show" x-cloak
    style="margin-top:8px;padding:10px 14px;background:#F5F3FF;border:1px solid #DDD6FE;border-radius:8px;display:flex;align-items:flex-start;gap:8px;">
    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="#7C3AED" stroke-width="2" style="flex-shrink:0;margin-top:1px;">
        <circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>
    </svg>
    <span style="font-size:11.5px;color:#5B21B6;line-height:1.6;flex:1;">{{ $text }}</span>
    <button x-on:click="show = false; localStorage.setItem('krd-tip-dismissed-{{ $id }}', '1')"
        style="background:none;border:none;cursor:pointer;color:#7C3AED;padding:0;flex-shrink:0;">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
</div>