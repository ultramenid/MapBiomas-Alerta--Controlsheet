<div x-data="{ all: false }" class="dark:text-slate-400">
    {{-- Section header --}}
    <div class="flex flex-wrap items-center gap-x-4 gap-y-2 mb-4">
        <div class="text-label text-stone-600 dark:text-slate-400">Alert by Auditor</div>
        <div class="flex-1"></div>
        <button type="button" @click="wide = wide === 'auditor' ? null : 'auditor'"
                :title="wide === 'auditor' ? 'Collapse' : 'Expand to full width'"
                class="text-stone-500 dark:text-slate-400 rounded-sm border border-stone-200 dark:border-slate-600 p-1.5 hover:bg-stone-100 dark:hover:bg-slate-800 cursor-pointer">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      :d="wide === 'auditor' ? 'M8 4v4H4M16 4v4h4M8 20v-4H4M16 20v-4h4' : 'M4 8V4h4M16 4h4v4M20 16v4h-4M8 20H4v-4'" />
            </svg>
        </button>
    </div>

    {{-- Skeleton: a real <table> mirroring the loaded table's column structure
         (same w-52 name, w-14 day, w-20 total widths and same $dates column count)
         and row count (capped at the 5 default-visible rows), so its width and
         height match the table that replaces it. The date headers render the
         actual "d M" text as a transparent pulsing block, which keeps the day
         columns exactly as wide as the real ones. wire:loading.remove below only
         toggles display, so the real table's Alpine state survives the swap. --}}
    <div wire:loading.delay class="overflow-auto no-scrollbar border border-stone-200 dark:border-slate-700 rounded-sm">
        <table class="w-full min-w-max text-xs border-collapse">
            <thead class="sticky top-0 z-30">
                <tr class="bg-stone-100 dark:bg-slate-800 border-b border-stone-200 dark:border-slate-700">
                    <th rowspan="2" class="w-52 min-w-52 sticky left-0 z-30 bg-stone-100 dark:bg-slate-800 px-3 py-2 border-r border-stone-200 dark:border-slate-700">
                        <div class="h-3 w-3/4 rounded-sm bg-stone-200 dark:bg-slate-700 animate-pulse"></div>
                    </th>
                    @foreach ($dates as $d)
                        <th class="px-3 py-2 text-center whitespace-nowrap border-l border-stone-200 dark:border-slate-700">
                            <span class="inline-block text-transparent bg-stone-200 dark:bg-slate-700 animate-pulse rounded-sm">{{ \Carbon\Carbon::parse($d)->format('d M') }}</span>
                        </th>
                    @endforeach
                    <th class="w-20 sticky right-0 z-20 bg-stone-200 dark:bg-slate-700 px-3 py-2 border-l border-stone-300 dark:border-slate-600">
                        <div class="h-3 mx-auto w-2/3 rounded-sm bg-stone-200 dark:bg-slate-700 animate-pulse"></div>
                    </th>
                </tr>
                <tr class="bg-stone-100 dark:bg-slate-800 border-b border-stone-200 dark:border-slate-700">
                    @foreach ($dates as $d)
                        <th class="w-14 px-3 py-1.5 border-l border-stone-200 dark:border-slate-700">
                            <div class="h-2.5 w-full rounded-sm bg-stone-200 dark:bg-slate-700 animate-pulse"></div>
                        </th>
                    @endforeach
                    <th class="w-20 sticky right-0 z-20 bg-stone-200 dark:bg-slate-700 px-3 py-1.5 border-l border-stone-300 dark:border-slate-600">
                        <div class="h-2.5 mx-auto w-2/3 rounded-sm bg-stone-200 dark:bg-slate-700 animate-pulse"></div>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-200 dark:divide-slate-700">
                @foreach (range(1, min(max(count($results), 1), 5)) as $i)
                    <tr>
                        <td class="w-52 min-w-52 sticky left-0 z-10 bg-white dark:bg-slate-900 px-3 py-2 border-r border-stone-200 dark:border-slate-700">
                            <div class="h-3.5 w-full rounded-sm bg-stone-200 dark:bg-slate-700 animate-pulse"></div>
                        </td>
                        @foreach ($dates as $d)
                            <td class="px-3 py-2 border-l border-stone-200 dark:border-slate-700">
                                <div class="h-3.5 mx-auto w-6 rounded-sm bg-stone-200 dark:bg-slate-700 animate-pulse"></div>
                            </td>
                        @endforeach
                        <td class="w-20 sticky right-0 z-10 bg-stone-100 dark:bg-slate-800 px-3 py-2 border-l border-stone-300 dark:border-slate-600">
                            <div class="h-3.5 mx-auto w-2/3 rounded-sm bg-stone-200 dark:bg-slate-700 animate-pulse"></div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Per-day matrix: auditor and total stay pinned, the days scroll between them --}}
    <div wire:loading.remove.delay>
    {{-- First 5 rows show by default; "Show all/less" reveals the rest. Slicing
         rows (not a height cap) avoids a cramped scroll and keeps the side-by-side
         auditor/validator cards the same height. Horizontal scroll (hidden) stays
         for the day columns; the sticky name/total columns pin. --}}
    <div class="overflow-auto no-scrollbar border border-stone-200 dark:border-slate-700 rounded-sm">
        <table class="w-full min-w-max text-xs border-collapse">
            <thead class="sticky top-0 z-30">
                <tr class="bg-stone-100 dark:bg-slate-800 border-b border-stone-200 dark:border-slate-700">
                    <th rowspan="2" class="w-52 min-w-52 sticky left-0 z-30 bg-stone-100 dark:bg-slate-800 text-left px-3 py-2 text-label text-stone-500 dark:text-slate-400 cursor-pointer border-r border-stone-200 dark:border-slate-700 hover:text-stone-900 dark:hover:text-slate-200"
                        wire:click="sortBy('name')">
                        Auditor @if ($dataField === 'name'){{ $dataOrder === 'asc' ? '▲' : '▼' }}@endif
                    </th>
                    @foreach ($dates as $d)
                        <th class="px-3 py-2 text-center text-label whitespace-nowrap cursor-pointer border-l border-stone-200 dark:border-slate-700 hover:text-stone-900 dark:hover:text-slate-200 {{ $dataField === $d ? 'text-stone-900 dark:text-slate-200' : 'text-stone-500 dark:text-slate-400' }}"
                            wire:click="sortBy('{{ $d }}')">
                            {{ \Carbon\Carbon::parse($d)->format('d M') }}
                            @if ($dataField === $d){{ $dataOrder === 'asc' ? '▲' : '▼' }}@endif
                        </th>
                    @endforeach
                    <th class="w-20 sticky right-0 z-20 bg-stone-200 dark:bg-slate-700 text-center px-3 py-2 text-label text-stone-600 dark:text-slate-300 cursor-pointer border-l border-stone-300 dark:border-slate-600"
                        wire:click="sortBy('total')">
                        Total @if ($dataField === 'total'){{ $dataOrder === 'asc' ? '▲' : '▼' }}@endif
                    </th>
                </tr>
                <tr class="bg-stone-100 dark:bg-slate-800 border-b border-stone-200 dark:border-slate-700 text-stone-500 dark:text-slate-500">
                    @foreach ($dates as $d)
                        <th class="w-14 px-3 py-1.5 text-center font-normal border-l border-stone-200 dark:border-slate-700">alerts</th>
                    @endforeach
                    <th class="w-20 sticky right-0 z-20 bg-stone-200 dark:bg-slate-700 px-3 py-1.5 text-center font-normal text-stone-600 dark:text-slate-300 border-l border-stone-300 dark:border-slate-600">alerts</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-200 dark:divide-slate-700">
                @forelse ($results as $row)
                    <tr x-show="all || {{ $loop->index }} < 5" class="group hover:bg-stone-50 dark:hover:bg-slate-800/60">
                        <td class="w-52 min-w-52 sticky left-0 z-10 bg-white dark:bg-slate-900 group-hover:bg-stone-50 dark:group-hover:bg-slate-800 px-3 py-2 align-middle border-r border-stone-200 dark:border-slate-700">
                            <a href="{{ url('/auditor-alert/'.$row['auditorId']) }}" class="block truncate font-medium text-green-700 dark:text-green-400 hover:underline">
                                {{ $row['auditorName'] }}
                            </a>
                        </td>

                        @foreach ($dates as $d)
                            @php $v = $row['daily'][$d] ?? 0; @endphp
                            <td class="px-3 py-2 text-center tabular-nums border-l border-stone-200 dark:border-slate-700 {{ $v ? 'text-stone-700 dark:text-slate-300' : 'text-stone-300 dark:text-slate-600' }} {{ $dataField === $d ? 'bg-stone-50 dark:bg-slate-800/40' : '' }}">
                                {{ $v }}
                            </td>
                        @endforeach

                        <td class="sticky right-0 z-10 bg-stone-100 dark:bg-slate-800 text-center px-3 py-2 tabular-nums font-bold text-stone-900 dark:text-slate-100 border-l border-stone-300 dark:border-slate-600">
                            {{ number_format($row['total']) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($dates) + 2 }}" class="px-3 py-10 text-center">
                            <div class="text-sm text-stone-500 dark:text-slate-400">No audits in this range</div>
                            <div class="text-xs text-stone-400 dark:text-slate-500 mt-1">Drag the slider below to widen the range.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if (count($results) > 5)
        <button type="button" @click="all = !all"
                class="mt-2 text-xs text-stone-500 dark:text-slate-400 hover:text-stone-800 dark:hover:text-slate-200 cursor-pointer">
            <span x-show="!all">Show all {{ count($results) }} auditors</span>
            <span x-show="all" x-cloak>Show less</span>
        </button>
    @endif
    </div>{{-- /wire:loading.remove --}}
</div>
