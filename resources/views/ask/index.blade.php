<x-app-layout>
    <div class="flex flex-col items-center justify-center min-h-[420px] px-8 py-10">
        <div class="w-[min(780px,92%)]">
            <form method="POST" action="{{ route('ask.store') }}">
                @csrf
                <input type="text"
                       name="text"
                       class="field-ask"
                       placeholder="Ask anything"
                       value="{{ $question?->text }}"
                       autofocus>
            </form>

            <div class="flex justify-between text-[12.5px] text-faint mt-[10px]">
                <span>{{ $passages }} passages across {{ $subjects }} subjects</span>
                <span>&crarr; asks</span>
            </div>

            @if ($examples->isNotEmpty())
                <div class="text-[13px] text-sub mt-[46px]">
                    Try —
                    @foreach ($examples as $example)
                        <a href="{{ route('ask.index', ['q' => $example]) }}">{{ $example }}</a>@if (! $loop->last) · @endif
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>