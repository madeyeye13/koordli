@props([
    'name'        => '',
    'wire'        => null,
    'selected'    => 'Select',
    'placeholder' => 'Select',
    'maxWidth'    => null,
])

<div
    class="krd-dropdown"
    x-data="krdDropdown({
        wire: '{{ $wire }}',
        selected: '{{ $selected }}',
        placeholder: '{{ $placeholder }}'
    })"
    x-on:click.outside="open = false"
    @if($maxWidth) style="max-width:{{ $maxWidth }};" @endif
>
    <button
        type="button"
        class="krd-dropdown-trigger"
        x-bind:class="{ open: open }"
        x-on:click="open = !open"
    >
        <span
            x-text="selected"
            :style="selected === placeholder ? 'color:#A8A29E' : ''"
        ></span>
        <svg class="krd-dropdown-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <polyline points="6 9 12 15 18 9"/>
        </svg>
    </button>

    <template x-if="open">
        <div class="krd-dropdown-menu">
            @if($placeholder)
            <div class="krd-dropdown-option" x-on:click="clear()">
                {{ $placeholder }}
            </div>
            @endif
            {{ $slot }}
        </div>
    </template>

</div>