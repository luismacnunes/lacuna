<x-app-layout>
    @if (! $question)

        {{-- Initial page --}}
        <div class="flex flex-col items-center justify-center min-h-[420px] px-8 py-10">
            <div class="w-[min(780px,92%)]">
                <form method="POST" action="{{ route('ask.store') }}">
                    @csrf
                    <input type="text" name="text" class="field-ask" placeholder="Ask anything" autofocus>
                </form>

                <div class="flex justify-between text-[12.5px] text-faint mt-[10px]">
                    <span>{{ $passages }} passages across {{ $subjects }} subjects</span>
                    <span>&crarr; asks</span>
                </div>
            </div>
        </div>

    @elseif ($question->answer)

        {{-- Answered, with material proof --}}
        <div class="max-w-answer mx-auto mt-[52px] px-8 pb-20">
            <div class="t-question border-b border-ink pb-[14px]">{{ $question->text }}</div>

            <div class="t-meta text-faint mt-[9px]">
                answered from {{ $question->answer->chunks->count() + $question->answer->curatedAnswers->count() }} passages
                · <a href="{{ route('ask.index') }}">ask another</a>
            </div>

            <div class="t-prose mt-11">{{ $question->answer->content }}</div>

            <div class="mt-14">
                <div class="flex items-baseline gap-3">
                    <span class="t-rule text-faint">Sources</span>
                    <span class="flex-1 h-px bg-line"></span>
                </div>

                @php $n = 0; @endphp

                @foreach ($question->answer->curatedAnswers as $curated)
                    @php $n++; @endphp
                    <x-source :index="$n"
                              :title="$curated->topic->name"
                              origin="curated"
                              :excerpt="$curated->answer"/>
                @endforeach

                @foreach ($question->answer->chunks as $chunk)
                    @php $n++; @endphp
                    <x-source :index="$n"
                              :title="$chunk->document->title"
                              origin="document"
                              :excerpt="$chunk->content"/>
                @endforeach
            </div>
        </div>

    @else

        {{-- No material to answer --}}
        <div class="max-w-gap mx-auto mt-[52px] px-8 pb-20">
            <div class="t-question border-b border-ink pb-[14px]">{{ $question->text }}</div>

            <div class="mt-[60px]">
                <x-mark :size="30" color="coral"/>
            </div>

            <div class="t-verdict mt-[18px]">Not in the material.</div>

            <div class="t-lead text-sub mt-[14px] max-w-[540px]">
                @if ($topic)
                    Nothing written answers this. As of just now, that's on record — filed under
                    <span class="font-semibold text-ink">{{ $topic->name }}</span>, top of the queue because a person asked.
                @else
                    Nothing written answers this. As of just now, that's on record.
                @endif
            </div>

            <div class="flex items-center gap-[26px] mt-10">
                @if ($pending)
                    <a href="{{ route('curate.edit', $pending) }}" class="btn">Answer it now</a>
                @endif
                <a href="{{ route('documents.create') }}" class="text-[13.5px] text-ink">Add material about it</a>
                <a href="{{ route('queue.index') }}" class="text-[13px] link-quiet">See the queue</a>
            </div>
        </div>

    @endif
</x-app-layout>