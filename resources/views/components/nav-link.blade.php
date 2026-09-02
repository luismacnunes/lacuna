@props([
    'href',
    'active' => false,
])

<a href="{{ $href }}"
   class="no-underline {{ $active ? 'text-ink' : 'text-faint' }}">
    {{ $slot }}
</a>