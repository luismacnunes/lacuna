<x-app-layout>
    <div class="max-w-coverage mx-auto mt-11 px-8 pb-20">
        <div class="t-title">Coverage</div>
        <div class="text-[13px] text-sub mt-1.5">
            The share of questions the base could answer, week by week. Ink is answered; coral is what it couldn't — each week's new gaps.
        </div>

        @if ($weeks->isEmpty())
            <p class="t-lead text-sub mt-12">
                Not enough questions yet to draw a line. Ask a few things and this fills in.
            </p>
        @else
            <div class="grid gap-1.5 items-end h-[300px] border-b border-line mt-12"
                style="grid-template-columns: repeat({{ $weeks->count() }}, minmax(0, 46px))">
                @foreach ($weeks as $week)
                    @php
                        $rate = $week['rate'];
                        $cut = 9;
                    @endphp

                    <svg viewBox="0 0 46 300" preserveAspectRatio="none" class="h-full w-full" title="...">

                        {{-- The share it couldn't answer --}}
                        <path d="M0 0 L{{ 46 - $cut }} 0 L46 {{ $cut }} L46 {{ 300 - ($rate * 3) }} L0 {{ 300 - ($rate * 3) }} Z"
                            fill="var(--coral)" fill-opacity="0.22"/>

                        {{-- The answered share --}}
                        <rect x="0" y="{{ 300 - ($rate * 3) }}" width="46" height="{{ $rate * 3 }}"
                            fill="var(--fill)"/>

                        {{-- The outline, over both --}}
                        <path d="M0 0 L{{ 46 - $cut }} 0 L46 {{ $cut }} L46 300 L0 300 Z"
                            fill="none" stroke="var(--coral)" stroke-width="1"
                            vector-effect="non-scaling-stroke"/>
                    </svg>
                @endforeach
            </div>

            <div class="grid gap-1.5 mt-2"
                 style="grid-template-columns: repeat({{ $weeks->count() }}, 1fr)">
                @foreach ($weeks as $i => $week)
                    <span class="text-[10.5px] text-faint whitespace-nowrap">
                        @if ($i === 0 || $i === $weeks->count() - 1 || $i % 4 === 0)
                            {{ $week['week']->format('j M') }}
                        @endif
                    </span>
                @endforeach
            </div>

            @php $last = $weeks->last(); @endphp
            <div class="text-[13px] text-sub mt-[30px]">
                Last week it answered {{ $last['answered'] }} of {{ $last['total'] }} — {{ $last['rate'] }}%.
                @if ($last['total'] > $last['answered'])
                    The {{ $last['total'] - $last['answered'] }} it couldn't are <a href="{{ route('queue.index') }}">in the queue</a>.
                @endif
            </div>
        @endif
    </div>
</x-app-layout>