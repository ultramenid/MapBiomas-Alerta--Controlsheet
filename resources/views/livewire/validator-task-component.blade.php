<div x-data="{ all: false }" class="dark:text-slate-400">
    {{-- Section header --}}
    <div class="flex flex-wrap items-center gap-x-4 gap-y-2 mb-4">
        <div class="text-label text-stone-600 dark:text-slate-400">Alert by Validator</div>
        <div class="flex-1"></div>
        {{-- toggle only exists in the role-0 side-by-side grid (scopeUserId is null there) --}}
        @if (!$scopeUserId)
        <button type="button" @click="wide = wide === 'validator' ? null : 'validator'"
                :title="wide === 'validator' ? 'Collapse' : 'Expand to full width'"
                class="text-stone-500 dark:text-slate-400 rounded-sm border border-stone-200 dark:border-slate-600 p-1.5 hover:bg-stone-100 dark:hover:bg-slate-800 cursor-pointer">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      :d="wide === 'validator' ? 'M8 4v4H4M16 4v4h4M8 20v-4H4M16 20v-4h4' : 'M4 8V4h4M16 4h4v4M20 16v4h-4M8 20H4v-4'" />
            </svg>
        </button>
        @endif
    </div>

    {{-- Skeleton: fills the card's full width (the real table lives in an
         overflow-auto container, so its visible width is always the card width
         — w-full matches that exactly) with a guessed row count for height.
         The two right-hand bars echo the validator table's task/approved totals.
         wire:loading.remove below only toggles display, so the table's modal
         (x-teleport) and Alpine state survive the swap. --}}
    <div wire:loading.delay class="w-full overflow-hidden border border-stone-200 dark:border-slate-700 rounded-sm">
        <div class="flex items-center gap-3 px-3 py-2 bg-stone-100 dark:bg-slate-800 border-b border-stone-200 dark:border-slate-700">
            <div class="h-3 w-28 rounded-sm bg-stone-200 dark:bg-slate-700 animate-pulse"></div>
            <div class="flex-1"></div>
            <div class="h-3 w-16 rounded-sm bg-stone-200 dark:bg-slate-700 animate-pulse"></div>
            <div class="h-3 w-16 rounded-sm bg-stone-200 dark:bg-slate-700 animate-pulse"></div>
        </div>
        @foreach (range(1, 6) as $i)
            <div class="flex items-center gap-3 px-3 py-2.5 border-b border-stone-100 dark:border-slate-800/60 last:border-0">
                <div class="h-3.5 w-44 shrink-0 rounded-sm bg-stone-200 dark:bg-slate-700 animate-pulse"></div>
                <div class="flex-1 h-3.5 rounded-sm bg-stone-200 dark:bg-slate-700 animate-pulse"></div>
                <div class="h-3.5 w-12 shrink-0 rounded-sm bg-stone-200 dark:bg-slate-700 animate-pulse"></div>
                <div class="h-3.5 w-12 shrink-0 rounded-sm bg-stone-200 dark:bg-slate-700 animate-pulse"></div>
            </div>
        @endforeach
    </div>

    {{-- Per-day matrix: task / approved per day, validator and totals stay pinned --}}
    <div wire:loading.remove.delay>
    {{-- First 5 rows show by default; "Show all/less" reveals the rest. Slicing
         rows (not a height cap) avoids a cramped scroll and keeps the side-by-side
         auditor/validator cards the same height. Horizontal scroll (hidden) stays
         for the day columns; the sticky name/total columns pin. --}}
    <div class="overflow-auto no-scrollbar border border-stone-200 dark:border-slate-700 rounded-sm">
        <table class="w-full min-w-max text-xs border-collapse">
            <thead class="sticky top-0 z-30">
                <tr class="bg-stone-100 dark:bg-slate-800 border-b border-stone-200 dark:border-slate-700">
                    <th rowspan="2" class="w-52 min-w-52 sticky left-0 z-30 bg-stone-100 dark:bg-slate-800 text-left px-3 py-2 text-label text-stone-500 dark:text-slate-400 border-r border-stone-200 dark:border-slate-700">
                        Validator
                    </th>
                    @foreach ($report['dates'] as $date)
                        <th colspan="2" class="px-3 py-2 text-center whitespace-nowrap text-label text-stone-500 dark:text-slate-400 border-l border-stone-200 dark:border-slate-700">
                            {{ \Carbon\Carbon::parse($date)->format('d M') }}
                        </th>
                    @endforeach
                    <th colspan="2" class="sticky right-0 z-20 bg-stone-200 dark:bg-slate-700 text-center px-3 py-2 text-label text-stone-600 dark:text-slate-300 border-l border-stone-300 dark:border-slate-600">
                        Total
                    </th>
                </tr>
                <tr class="bg-stone-100 dark:bg-slate-800 border-b border-stone-200 dark:border-slate-700 text-stone-500 dark:text-slate-500">
                    @foreach ($report['dates'] as $date)
                        <th class="w-14 px-3 py-1.5 text-center font-normal border-l border-stone-200 dark:border-slate-700">task</th>
                        <th class="w-14 px-3 py-1.5 text-center font-normal">appr.</th>
                    @endforeach
                    <th class="w-20 sticky right-20 z-20 bg-stone-200 dark:bg-slate-700 px-3 py-1.5 text-center font-normal text-stone-600 dark:text-slate-300 border-l border-stone-300 dark:border-slate-600">task</th>
                    <th class="w-20 sticky right-0 z-20 bg-stone-200 dark:bg-slate-700 px-3 py-1.5 text-center font-normal text-green-700 dark:text-green-400">appr.</th>
                </tr>
            </thead>

            @forelse ($report['data'] as $row)
                <tbody x-data="{ open: false }" x-show="all || {{ $loop->index }} < 5" class="border-t border-stone-200 dark:border-slate-700">
                    <tr @click="open = true" class="group cursor-pointer hover:bg-stone-50 dark:hover:bg-slate-800/60">
                        <td class="w-52 min-w-52 sticky left-0 z-10 bg-white dark:bg-slate-900 group-hover:bg-stone-50 dark:group-hover:bg-slate-800 px-3 py-2 align-middle border-r border-stone-200 dark:border-slate-700">
                            <div class="flex items-center gap-1.5 whitespace-nowrap font-medium text-green-700 dark:text-green-400">
                                <svg class="w-3.5 h-3.5 shrink-0 text-stone-300 dark:text-slate-600 group-hover:text-stone-500 dark:group-hover:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4h4M16 4h4v4M20 16v4h-4M8 20H4v-4" />
                                </svg>
                                <span class="truncate">{{ $row['validatorName'] }}</span>
                            </div>

                            {{-- breakdown modal; teleported out of the .glass card so `fixed` means the viewport --}}
                            <template x-teleport="body">
                                <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4"
                                     @keydown.escape.window="open = false">
                                    <div x-show="open" x-transition.opacity class="absolute inset-0 bg-stone-900/40 dark:bg-black/70" @click="open = false"></div>

                                    @php
                                        $cats = [
                                            'Insert' => 'Inserted',
                                            'reclassification' => 'Reclassified',
                                            'reexportimage' => 'Re-exported',
                                            'refined' => 'Refined',
                                            'Reject' => 'Rejected',
                                            'approved' => 'Approved',
                                        ];
                                        // bars are a share of the task total above them, not of the biggest
                                        // category — otherwise the largest one always reads as a full bar
                                        $den = max(1, (int) ($row['grandTotal'] ?? 0));
                                        $rate = ($row['grandTotal'] ?? 0) ? round(($row['grandApproved'] ?? 0) / $row['grandTotal'] * 100) : 0;
                                    @endphp

                                    <div x-show="open" x-transition
                                         class="relative w-full max-w-md rounded-sm border border-stone-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-xl">
                                        <div class="flex items-start gap-4 px-5 py-4 border-b border-stone-200 dark:border-slate-700">
                                            <div class="min-w-0">
                                                <div class="text-sm font-semibold text-stone-900 dark:text-slate-100 truncate">{{ $row['validatorName'] }}</div>
                                                <div class="text-xs text-stone-400 dark:text-slate-500 tabular-nums mt-0.5">{{ $rangeValidator }}</div>
                                            </div>
                                            <div class="flex-1"></div>
                                            <button type="button" @click="open = false" aria-label="Close"
                                                    class="shrink-0 text-stone-400 dark:text-slate-500 hover:text-stone-700 dark:hover:text-slate-300 cursor-pointer">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>

                                        <div class="grid grid-cols-2 divide-x divide-stone-200 dark:divide-slate-700 border-b border-stone-200 dark:border-slate-700">
                                            <div class="px-5 py-3">
                                                <div class="text-[10px] uppercase tracking-wide text-stone-400 dark:text-slate-500">Tasks</div>
                                                <div class="text-xl font-semibold tabular-nums text-stone-900 dark:text-slate-100">{{ number_format($row['grandTotal'] ?? 0) }}</div>
                                            </div>
                                            <div class="px-5 py-3">
                                                <div class="text-[10px] uppercase tracking-wide text-stone-400 dark:text-slate-500">Approved</div>
                                                <div class="text-xl font-semibold tabular-nums text-green-700 dark:text-green-400">
                                                    {{ number_format($row['grandApproved'] ?? 0) }}
                                                    <span class="text-xs font-normal text-stone-400 dark:text-slate-500">{{ $rate }}%</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="px-5 py-4 space-y-2">
                                            {{-- column captions, aligned to the rows below --}}
                                            <div class="flex items-center gap-3 pb-1.5 mb-1 border-b border-stone-200 dark:border-slate-700 text-[10px] uppercase tracking-wide text-stone-400 dark:text-slate-500">
                                                <span class="w-24 shrink-0">Action</span>
                                                <span class="flex-1 min-w-0 normal-case tracking-normal text-stone-400 dark:text-slate-500">share of {{ number_format($row['grandTotal'] ?? 0) }} tasks</span>
                                                <span class="w-8 shrink-0 text-right">%</span>
                                                <span class="w-10 shrink-0 text-right">Alerts</span>
                                            </div>
                                            @foreach ($cats as $key => $label)
                                                @php
                                                    $n = (int) ($row['category'][$key] ?? 0);
                                                    $pct = min(100, round($n / $den * 100));
                                                @endphp
                                                <div class="flex items-center gap-3 text-xs">
                                                    <span class="w-24 shrink-0 {{ $n ? 'text-stone-600 dark:text-slate-300' : 'text-stone-400 dark:text-slate-600' }}">{{ $label }}</span>
                                                    <span class="flex-1 h-1.5 rounded-sm bg-stone-100 dark:bg-slate-800 overflow-hidden">
                                                        <span class="block h-full rounded-sm {{ $key === 'approved' ? 'bg-green-600 dark:bg-green-500' : ($key === 'Reject' ? 'bg-red-400 dark:bg-red-500/80' : 'bg-stone-400 dark:bg-slate-400') }}"
                                                              style="width: {{ $n ? max(2, $pct) : 0 }}%"></span>
                                                    </span>
                                                    <span class="w-8 shrink-0 text-right tabular-nums text-stone-400 dark:text-slate-500">{{ $n ? $pct.'%' : '' }}</span>
                                                    <span class="w-10 shrink-0 text-right font-semibold tabular-nums {{ $key === 'approved' ? 'text-green-700 dark:text-green-400' : ($n ? 'text-stone-800 dark:text-slate-200' : 'text-stone-300 dark:text-slate-600') }}">{{ number_format($n) }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </td>

                        @foreach ($report['dates'] as $date)
                            @php
                                $task = (int) ($row['dates'][$date]['task'] ?? 0);
                                $appr = (int) ($row['dates'][$date]['approved'] ?? 0);
                            @endphp
                            <td class="px-3 py-2 text-center tabular-nums border-l border-stone-200 dark:border-slate-700 {{ $task ? 'text-stone-700 dark:text-slate-300' : 'text-stone-300 dark:text-slate-600' }}">{{ $task }}</td>
                            <td class="px-3 py-2 text-center tabular-nums bg-green-50 dark:bg-green-900/20 {{ $appr ? 'text-green-800 dark:text-green-300' : 'text-stone-300 dark:text-slate-600' }}">{{ $appr }}</td>
                        @endforeach

                        <td class="w-20 sticky right-20 z-10 bg-stone-100 dark:bg-slate-800 px-3 py-2 text-center tabular-nums font-bold text-stone-900 dark:text-slate-100 border-l border-stone-300 dark:border-slate-600">
                            {{ number_format($row['grandTotal'] ?? 0) }}
                        </td>
                        <td class="w-20 sticky right-0 z-10 bg-stone-200 dark:bg-slate-700 px-3 py-2 text-center tabular-nums font-bold text-green-800 dark:text-green-300">
                            {{ number_format($row['grandApproved'] ?? 0) }}
                        </td>
                    </tr>

                </tbody>
            @empty
                <tbody>
                    <tr>
                        <td colspan="{{ max(count($report['dates']) * 2 + 3, 2) }}" class="px-3 py-10 text-center">
                            <div class="text-sm text-stone-500 dark:text-slate-400">No validator activity in this range</div>
                            <div class="text-xs text-stone-400 dark:text-slate-500 mt-1">Drag the slider below to widen the range.</div>
                        </td>
                    </tr>
                </tbody>
            @endforelse
        </table>
    </div>
    @if (count($report['data']) > 5)
        <button type="button" @click="all = !all"
                class="mt-2 text-xs text-stone-500 dark:text-slate-400 hover:text-stone-800 dark:hover:text-slate-200 cursor-pointer">
            <span x-show="!all">Show all {{ count($report['data']) }} validators</span>
            <span x-show="all" x-cloak>Show less</span>
        </button>
    @endif
    </div>{{-- /wire:loading.remove --}}
</div>
