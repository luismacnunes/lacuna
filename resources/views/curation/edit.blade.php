<x-app-layout>
    <div class="max-w-curate mx-auto mt-11 px-8 pb-20">
        <a href="{{ route('queue.index') }}" class="t-meta text-faint no-underline">&larr; Queue</a>

        <div class="t-question-sm border-b border-ink pb-[13px] mt-5">{{ $pending->text }}</div>

        <div class="t-meta text-faint mt-2">
            @if ($pending->origin === App\Enums\PendingOrigin::RealFailure)
                asked {{ $pending->created_at->diffForHumans() }}
            @else
                generated from {{ $pending->document?->title ?? 'an upload' }}
            @endif
            · {{ $pending->topic->name }}
        </div>

        @if ($errors->any())
            <ul class="t-meta text-coral mt-6">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <form method="POST" action="{{ route('curate.update', $pending) }}">
            @csrf
            @method('PUT')

            <div class="t-label mt-[42px]">The answer</div>
            <textarea name="answer"
                      class="field-area"
                      placeholder="Write it the way you'd tell a colleague."
                      autofocus>{{ old('answer') }}</textarea>
            <div class="text-xs text-faint mt-2">
                It gets indexed the moment you save — the next person who asks gets this.
            </div>

            <div class="t-label mt-[34px]">Files under</div>
            <input type="text"
                   name="topic_name"
                   value="{{ old('topic_name', $pending->topic->name) }}"
                   class="field w-[300px]">
            <div class="text-xs text-faint mt-[7px]">
                Suggested from where it lands on the map — correct it if it's wrong.
            </div>

            <div class="flex items-baseline justify-between mt-11">
                <button type="submit" class="btn">Save — it answers from now on</button>
            </div>
        </form>

        <form method="POST" action="{{ route('curate.dismiss', $pending) }}" class="mt-4">
            @csrf
            @method('DELETE')
            <button type="submit" class="t-meta link-quiet bg-transparent border-none p-0 underline">
                Not worth answering — dismiss
            </button>
        </form>
    </div>
</x-app-layout>