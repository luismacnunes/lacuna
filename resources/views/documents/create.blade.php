<x-app-layout>
    <div class="max-w-curate mx-auto mt-11 px-8 pb-20">
        <div class="t-title">Add material</div>
        <div class="text-[13px] text-sub mt-1.5">
            Paste text or bring a file. It gets sliced into passages and lands on the map where it belongs.
        </div>

        @if (session('status'))
            <p class="t-meta text-sub mt-6">{{ session('status') }}</p>
        @endif

        @if ($errors->any())
            <ul class="t-meta text-coral mt-6">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <form method="POST" action="{{ route('documents.store') }}">
            @csrf

            <textarea name="content"
                      class="field-area min-h-[200px] mt-6 text-[15.5px] leading-[1.6]"
                      placeholder="Paste anything — a runbook, a decision, the email where it got decided."
                      required autofocus>{{ old('content') }}</textarea>

            <div class="t-label mt-[34px]">What is it, in one line</div>
            <input type="text" name="title" value="{{ old('title') }}" class="field" required>
            <div class="text-xs text-faint mt-[7px]">A short description someone would recognise it by.</div>

            <div class="t-label mt-[34px]">Files under</div>
            <input type="text" name="topic" value="{{ old('topic') }}" class="field w-[300px]" required>
            <div class="text-xs text-faint mt-[7px]">A subject name. Reuse one that exists, or start a new one.</div>

            <div class="mt-10">
                <button type="submit" class="btn">Index it</button>
            </div>
        </form>
    </div>
</x-app-layout>