<x-app-layout>
    <div class="max-w-curate mx-auto mt-11 px-8 pb-20">
        <a href="{{ route('review.index') }}" class="t-meta text-faint no-underline">&larr; Review</a>

        <div class="t-question-sm border-b border-ink pb-[13px] mt-5">{{ $answer->question }}</div>

        <div class="t-meta text-faint mt-2">
            existing answer · its source changed — fix what aged
        </div>

        @if ($errors->any())
            <ul class="t-meta text-coral mt-6">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <form method="POST" action="{{ route('review.update', $answer) }}">
            @csrf
            @method('PUT')

            <div class="t-label mt-[42px]">The answer</div>
            <textarea name="answer" class="field-area" autofocus>{{ old('answer', $answer->answer) }}</textarea>
            <div class="text-xs text-faint mt-2">
                It gets re-indexed the moment you save — the next person who asks gets this.
            </div>

            <div class="t-label mt-[34px]">Files under</div>
            <div class="text-[15px] text-ink pt-[7px]">{{ $answer->topic->name }}</div>

            <div class="mt-11">
                <button type="submit" class="btn">Save the corrected answer</button>
            </div>
        </form>
    </div>
</x-app-layout>