<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ isset($title) ? $title . ' — Lacuna' : 'Lacuna' }}</title>

    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="alternate icon" href="/favicon-32.png" sizes="32x32">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>

<header class="flex items-center px-9 py-[18px] border-b border-line">
    <a href="{{ route('ask.index') }}" class="flex items-center gap-[11px] no-underline">
        <x-mark :size="20"/>
        <span class="t-wordmark">Lacuna</span>
    </a>

    <nav class="ml-auto flex gap-[26px] text-sm font-medium items-baseline">
        <x-nav-link :href="route('ask.index')" :active="request()->routeIs('ask.*')">
            Ask
        </x-nav-link>

        <x-nav-link :href="route('queue.index')" :active="request()->routeIs('queue.*', 'curate.*')">
            Queue @if ($openCount ?? 0) {{ $openCount }} @endif
        </x-nav-link>

        <x-nav-link :href="route('review.index')" :active="request()->routeIs('review.*')">
            Review @if ($reviewCount ?? 0) {{ $reviewCount }} @endif
        </x-nav-link>

        <x-nav-link :href="route('metrics.index')" :active="request()->routeIs('metrics.*')">
            Coverage
        </x-nav-link>

        <x-nav-link :href="route('documents.create')" :active="request()->routeIs('documents.*')">
            Add material
        </x-nav-link>

        <x-nav-link :href="route('map.index')" :active="request()->routeIs('map.*')">
            Map
        </x-nav-link>
    </nav>
</header>

{{ $slot }}

</body>
</html>