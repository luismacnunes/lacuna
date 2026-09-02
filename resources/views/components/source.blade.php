@props(['index', 'title', 'origin', 'excerpt'])

<div class="grid grid-cols-[26px_1fr] py-[18px] pb-4 border-b border-line">
    <span class="t-meta text-faint">{{ $index }}</span>
    <span>
        <span class="flex justify-between items-baseline gap-4">
            <span class="text-sm font-semibold text-ink">{{ $title }}</span>
            <span class="t-meta-sm text-faint whitespace-nowrap">{{ $origin }}</span>
        </span>
        <span class="block t-excerpt text-sub mt-1.5">{{ Str::limit($excerpt, 220) }}</span>
    </span>
</div>