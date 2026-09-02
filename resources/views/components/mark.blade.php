@props([
    'size' => 20,
    'color' => 'ink',
    'marker' => false,
])

@php
    // Full Mark
    $full = 'M0 0 L69 0 L100 31 L100 100 L31 100 L0 69 Z M24 21 L44 21 L44 58 L79 58 L79 79 L24 79 Z';

    // Solid Mark
    $solid = 'M0 0 L69 0 L100 31 L100 100 L31 100 L0 69 Z';
@endphp

<svg viewBox="0 0 100 100"
     width="{{ $size }}"
     height="{{ $size }}"
     {{ $attributes->merge(['class' => 'flex-none']) }}
     @if (! $marker) role="img" aria-label="Lacuna" @else aria-hidden="true" @endif>
    <path style="fill: var(--{{ $color }})"
          @if (! $marker) fill-rule="evenodd" @endif
          d="{{ $marker ? $solid : $full }}"/>
</svg>