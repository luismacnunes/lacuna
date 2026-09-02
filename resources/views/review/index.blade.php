<x-app-layout>
    <div class="max-w-review mx-auto mt-11 px-8 pb-20">
        <div class="t-title">Review</div>
        <div class="text-[13px] text-sub mt-1.5">
            Answers whose sources changed since they were written. One look each — confirm, fix, or drop.
        </div>

        @if (session('status'))
            <p class="t-meta text-sub mt-6">{{ session('status') }}</p>
        @endif

        <div class="mt-[30px]">
            @forelse ($answers as $answer)
                <div class="flex items-center gap-5 py-4 border-t border-line">
                    <span class="flex-1 min-w-0">
                        <span class="block text-[14.5px] font-semibold text-ink">{{ $answer->question }}</span>
                        <span class="block text-[13px] text-sub mt-1">
                            &ldquo;{{ $answer->flaggedByDocument?->title ?? 'A source document' }}&rdquo; changed
                        </span>
                        <span class="block t-meta-sm text-faint mt-[3px]">
                            changed {{ $answer->flagged_at->diffForHumans() }}
                            · answer written {{ $answer->created_at->diffForHumans() }}
                            @if ($answer->author) by {{ $answer->author->name }} @endif
                        </span>
                    </span>

                    <form method="POST" action="{{ route('review.confirm', $answer) }}">
                        @csrf
                        <button type="submit" class="btn-outline">Still fine</button>
                    </form>

                    <a href="{{ route('review.edit', $answer) }}" class="text-[13px] text-sub">Edit</a>

                    <form method="POST" action="{{ route('review.destroy', $answer) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="t-meta link-quiet bg-transparent border-none p-0 underline">Remove</button>
                    </form>
                </div>
            @empty
                <p class="t-lead text-sub">
                    Nothing waiting. When a source document changes underneath an answer, it shows up here.
                </p>
            @endforelse
        </div>
    </div>
</x-app-layout>