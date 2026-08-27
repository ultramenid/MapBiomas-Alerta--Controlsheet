{{-- Alert lookup: type an ID, get the whole handling story (admin only).
     Its own component so a keystroke never re-renders the chart card.

     The search input lives INSIDE the dialog on purpose: showModal() pulls
     focus into the top layer, so an input left outside would lose focus the
     moment the first result opened the modal. --}}
<div class="mb-5 dark:text-slate-400" x-data="{ trail: false }">

    <button type="button" @click="$refs.dlg.showModal()"
            class="inline-flex items-center gap-2 rounded-sm border border-stone-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-xs text-stone-600 dark:text-slate-300 hover:border-stone-400 dark:hover:border-slate-500 cursor-pointer">
        <svg class="w-3.5 h-3.5 text-stone-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        Who handled an alert?
    </button>

    {{-- native <dialog>: backdrop, Esc-to-close and focus trap for free, and the
         top layer clears the sticky filter's z-30 without a z-index fight.
         wire:ignore.self stops the morph stripping `open` on every keystroke. --}}
    <dialog x-ref="dlg" wire:ignore.self
            @close="$wire.clearFind()" @click.self="$refs.dlg.close()"
            class="m-auto w-full max-w-2xl bg-transparent p-0 backdrop:bg-stone-900/40 dark:backdrop:bg-black/70">
        <div class="max-h-[85vh] overflow-y-auto rounded-sm border border-stone-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-5 dark:text-slate-400">

            <div class="flex items-start gap-3 mb-4">
                <div class="relative flex-1">
                    <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-stone-400 dark:text-slate-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" autofocus wire:model.live.debounce.400ms="alertCode"
                           class="bg-white dark:bg-slate-800 border border-stone-300 dark:border-slate-600 text-stone-900 dark:text-slate-100 w-full rounded-sm pl-8 pr-16 py-2 text-xs focus:outline-none focus:ring-1 focus:ring-stone-400 dark:focus:ring-slate-500"
                           placeholder="Type an alert ID">
                    <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[10px] text-stone-400 dark:text-slate-500">
                        <span wire:loading wire:target="alertCode">searching…</span>
                        <span wire:loading.remove wire:target="alertCode">
                            @if ($lookup)
                                <button type="button" wire:click="clearFind" class="hover:text-stone-700 dark:hover:text-slate-300 cursor-pointer">clear</button>
                            @endif
                        </span>
                    </span>
                </div>
                <button type="button" @click="$refs.dlg.close()" aria-label="Close"
                        class="shrink-0 rounded-sm p-2 text-stone-400 dark:text-slate-500 hover:bg-stone-100 dark:hover:bg-slate-800 hover:text-stone-700 dark:hover:text-slate-300 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            @if (! $lookup)
                <p class="text-xs text-stone-400 dark:text-slate-500 py-6 text-center">
                    Enter at least 3 characters of an alert ID to see who audited and validated it.
                </p>
            @elseif (! $lookup['found'])
                <p class="text-xs text-stone-500 dark:text-slate-400 py-6 text-center">
                    <span class="font-medium text-stone-700 dark:text-slate-300">{{ $lookup['code'] }}</span> — no alert and no log entry with this ID.
                </p>
            @else
                @php
                    $st = $lookup['status'];
                    $tone = match ($st) {
                        'approved' => 'text-green-700 dark:text-green-400 border-green-300 dark:border-green-700',
                        'rejected', 'error' => 'text-red-700 dark:text-red-400 border-red-300 dark:border-red-800',
                        'pre-approved', 'refined' => 'text-amber-700 dark:text-amber-400 border-amber-300 dark:border-amber-700',
                        'reexportimage', 'reclassification' => 'text-blue-700 dark:text-blue-400 border-blue-300 dark:border-blue-800',
                        default => 'text-stone-600 dark:text-slate-400 border-stone-300 dark:border-slate-600',
                    };
                @endphp

                {{-- headline: id + status + where it is --}}
                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 pb-3 border-b border-stone-200 dark:border-slate-700">
                    <span class="text-sm font-semibold tabular-nums text-stone-900 dark:text-slate-100">{{ $lookup['code'] }}</span>
                    @if ($st)
                        <span class="text-[11px] rounded-sm border px-2 py-0.5 {{ $tone }}">{{ $st }}</span>
                    @endif
                    @if ($lookup['alert'])
                        <span class="text-xs text-stone-500 dark:text-slate-400">{{ $lookup['alert']['province'] }} · {{ $lookup['alert']['region'] }}</span>
                        <span class="text-xs text-stone-400 dark:text-slate-500 tabular-nums">detected {{ $lookup['alert']['detected'] }}</span>
                    @else
                        <span class="text-xs text-stone-400 dark:text-slate-500">logged, but no active alert row</span>
                    @endif
                </div>

                {{-- the two answers people actually came for: who audited, who validated --}}
                @php
                    $people = [
                        ['role' => 'Auditor',   'who' => $lookup['auditor'],   'url' => '/auditor-alert/', 'empty' => 'Not audited yet'],
                        ['role' => 'Validator', 'who' => $lookup['validator'], 'url' => '/alertanalis/',   'empty' => 'Not validated yet'],
                    ];
                @endphp
                <div class="grid gap-4 sm:gap-0 sm:grid-cols-2 pt-3">
                    @foreach ($people as $i => $p)
                        <div class="{{ $i ? 'sm:pl-5 sm:border-l sm:border-stone-200 sm:dark:border-slate-700' : 'sm:pr-5' }}">
                            <div class="text-[10px] uppercase tracking-wide text-stone-400 dark:text-slate-500 mb-1">{{ $p['role'] }}</div>
                            @if ($p['who'])
                                <a href="{{ url($p['url'].$p['who']['id']) }}"
                                   class="text-sm font-medium text-green-700 dark:text-green-400 underline decoration-green-700/40 dark:decoration-green-400/40 underline-offset-4 hover:decoration-current">{{ $p['who']['name'] }}</a>
                            @else
                                <div class="text-sm text-stone-400 dark:text-slate-500 italic">{{ $p['empty'] }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>

                {{-- everything else that happened to it --}}
                @if (count($lookup['history']))
                <div class="pt-3 mt-3 border-t border-stone-200 dark:border-slate-700">
                    <button type="button" @click="trail = !trail" class="flex items-center gap-1.5 text-xs text-stone-500 dark:text-slate-400 hover:text-stone-800 dark:hover:text-slate-200 cursor-pointer">
                        <svg class="w-3 h-3 transition-transform duration-150" :class="{ 'rotate-90': trail }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        {{ count($lookup['history']) }} {{ Str::plural('action', count($lookup['history'])) }} on this alert
                    </button>
                    <div x-show="trail" style="display: none;" class="mt-2 space-y-1">
                        @foreach ($lookup['history'] as $h)
                            <div class="flex items-baseline gap-2 text-xs">
                                <span class="w-32 shrink-0 tabular-nums text-stone-400 dark:text-slate-500">{{ \Carbon\Carbon::parse($h['at'])->format('d M Y H:i') }}</span>
                                <span class="w-40 shrink-0 truncate text-stone-700 dark:text-slate-300">{{ $h['name'] }}</span>
                                <span class="text-stone-500 dark:text-slate-400">{{ $h['action'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            @endif
        </div>
    </dialog>
</div>
