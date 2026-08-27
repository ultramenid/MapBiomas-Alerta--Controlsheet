<div class="dark:text-slate-400">
    {{-- Section header with date badge --}}
    <div class="mb-4">
        <div class="text-label text-stone-600 dark:text-slate-400">My Audits</div>
    </div>

    <div wire:loading.delay class="w-full bg-stone-900 dark:bg-slate-200 h-0.5 animate-pulse rounded-sm mb-2"></div>

    {{-- One row per day, newest first --}}
    <div class="border border-stone-200 dark:border-slate-700 rounded-sm">
        <div class="flex items-center gap-3 px-3 py-2 bg-stone-100 dark:bg-slate-800 border-b border-stone-200 dark:border-slate-700 text-label text-stone-500 dark:text-slate-400">
            <span class="flex-1">Date</span>
            <span class="w-24 shrink-0 text-right">Alerts audited</span>
        </div>

        <div class="divide-y divide-stone-200 dark:divide-slate-700 max-h-96 overflow-y-auto no-scrollbar">
            @forelse ($results as $date => $count)
                <div class="flex items-center gap-3 px-3 py-2 hover:bg-stone-50 dark:hover:bg-slate-800/60">
                    <span class="flex-1 text-sm tabular-nums {{ $count ? 'text-stone-700 dark:text-slate-300' : 'text-stone-400 dark:text-slate-600' }}">
                        {{ \Carbon\Carbon::parse($date)->format('d M Y') }}
                    </span>
                    <span class="w-24 shrink-0 text-right text-sm font-semibold tabular-nums {{ $count ? 'text-stone-900 dark:text-slate-100' : 'text-stone-300 dark:text-slate-600' }}">{{ $count }}</span>
                </div>
            @empty
                <div class="px-3 py-10 text-center">
                    <div class="text-sm text-stone-500 dark:text-slate-400">No audits in this range</div>
                    <div class="text-xs text-stone-400 dark:text-slate-500 mt-1">Drag the slider below to widen the range.</div>
                </div>
            @endforelse
        </div>
    </div>
</div>
