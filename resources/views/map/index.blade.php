@php
    $asked = $gaps->count();
    $frontier = $gaps->where('isolated', true);
    $holes = $gaps->where('isolated', false);
@endphp

<x-app-layout>
    <div class="px-9 py-4 flex justify-between items-baseline">
        <span class="t-meta text-faint">
            <span class="text-ink font-semibold">{{ $total }}</span> passages ·
            <span class="text-ink font-semibold">{{ $subjects }}</span> subjects ·
            <span class="text-coral">■</span>
            <span class="text-ink font-semibold">{{ $asked }}</span> open gaps
        </span>
        <span class="t-meta text-faint">distance = relatedness · empty = unwritten</span>
    </div>

    <div class="flex gap-8 px-9 pb-16">
        <div class="flex-1 min-w-0">
            <svg viewBox="0 0 {{ $width }} {{ $height }}" class="w-full h-auto">
                @foreach ($points as $point)
                    <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="3"
                            fill="var(--faint)" opacity="0.55"/>
                @endforeach

                @foreach ($clusters as $cluster)
                    @if ($cluster['showLabel'])
                        <text x="{{ $cluster['cx'] }}" y="{{ $cluster['labelY'] }}"
                            text-anchor="middle" font-size="12" fill="var(--sub)">{{ $cluster['name'] }}</text>
                    @endif
                @endforeach

                @foreach ($gaps as $gap)
                    @if ($gap['isolated'])
                        <rect x="{{ $gap['x'] - 10 }}" y="{{ $gap['y'] - 10 }}" width="20" height="20"
                            fill="none" stroke="var(--coral)" stroke-width="1" stroke-dasharray="3 3"/>
                    @endif

                    <path transform="translate({{ $gap['x'] - 6 }},{{ $gap['y'] - 6 }}) scale(0.12)"
                        fill="var(--coral)" d="M0 0 L69 0 L100 31 L100 100 L31 100 L0 69 Z">
                        <title>{{ $gap['text'] }}</title>
                    </path>
                @endforeach
            </svg>
        </div>

        <div class="w-[320px] flex-none border-l border-line pl-8">
            <div class="text-sm font-semibold text-ink">At a glance</div>

            <div class="t-lead text-sub mt-4">
                <span class="text-ink font-semibold">{{ $total }}</span> written passages in
                <span class="text-ink font-semibold">{{ $subjects }}</span> subjects
            </div>

            <div class="t-lead text-sub mt-2">
                <span class="text-coral">■</span>
                <span class="text-ink font-semibold">{{ $asked }}</span> questions nobody could answer
            </div>

            <div class="text-[13px] text-faint mt-5">
                The empty regions aren't missing data. They're missing knowledge.
            </div>

            @if ($frontier->isNotEmpty())
                <div class="text-sm font-semibold text-ink mt-9">Unwritten territory</div>
                <div class="text-[13px] text-faint mt-1">nothing near them — answering takes real thought</div>

                @foreach ($frontier->take(3) as $gap)
                    <div class="border-b border-line py-4">
                        <div class="text-[15px] text-ink">{{ $gap['text'] }}</div>
                        <div class="t-meta-sm text-faint mt-1.5">nothing written anywhere near it</div>
                    </div>
                @endforeach
            @endif

            @if ($holes->isNotEmpty())
                <div class="text-sm font-semibold text-ink mt-9">Holes in covered subjects</div>
                <div class="text-[13px] text-faint mt-1">missing details in known ground — quick fills for whoever wrote the rest</div>

                @foreach ($holes->take(3) as $gap)
                    <div class="border-b border-line py-4">
                        <div class="text-[15px] text-ink">{{ $gap['text'] }}</div>
                        <div class="t-meta-sm text-faint mt-1.5">
                            @if ($gap['topic'])
                                at the edge of {{ $gap['topic'] }}
                            @else
                                close to material that exists
                            @endif
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</x-app-layout>