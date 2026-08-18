<div class="dark:text-slate-400">
    @php
        // Canonical status order + house colour family, shared by the breakdown and the log pills.
        $statuses = [
            'pending'          => ['Pending', 'stone'],
            'pre-approved'     => ['Pre-approved', 'blue'],
            'refined'          => ['Refined', 'sky'],
            'error'            => ['Error', 'red'],
            'reexportimage'    => ['Re-export image', 'amber'],
            'reclassification' => ['Re-classification', 'amber'],
            'approved'         => ['Approved', 'green'],
            'rejected'         => ['Rejected', 'red'],
            'duplicate'        => ['Duplicate', 'red'],
        ];

        $count = fn ($k) => (int) ($statusStats[$k] ?? 0);
        $total = (int) $statusStats->sum();

        $chartMax = max($chart['values'] ?: [0]);
        $hasSearch = $searchId !== null && $searchId !== '';

        $years = ['all' => 'All Years'] + collect(range((int) date('Y'), 2020))->mapWithKeys(fn ($y) => [$y => $y])->all();
        $months = ['all' => 'All Months'] + collect(['January','February','March','April','May','June','July','August','September','October','November','December'])
            ->mapWithKeys(fn ($m, $i) => [$i + 1 => $m])->all();
        $statusOptions = ['all' => 'All Status'] + collect($statuses)->map(fn ($s) => $s[0])->all();
        $actionOptions = [
            'all' => 'All Actions',
            'auditing' => 'Auditing',
            'reexportimage' => 'Re-export image',
            'reclassification' => 'Re-classification',
            'deleting' => 'Deleting',
            'Insert' => 'Insert',
            'refined' => 'Refined',
            'Reject' => 'Reject',
        ];
        $isScoped = $yearAlert !== 'all' || $monthAlert !== 'all' || $selectStatus !== 'all' || $selectAction !== 'all';

        $card = 'glass rounded-sm p-4 mb-4 z-20 relative dark:text-slate-400';
        $selectClass = 'w-full appearance-none bg-white dark:bg-slate-800 border border-stone-300 dark:border-slate-600 text-stone-900 dark:text-slate-100 px-3 py-1.5 text-sm rounded-sm focus:outline-none cursor-pointer transition-none';
        $chevron = 'absolute pointer-events-none right-3 top-2 size-4 text-stone-500';
        $ghostBtn = 'border border-stone-300 dark:border-slate-600 text-stone-700 dark:text-slate-300 py-1.5 px-3 text-sm font-semibold rounded-sm cursor-pointer hover:bg-stone-100 dark:hover:bg-slate-800 transition-none';
        $pill = 'inline-flex items-center justify-center text-center w-[9.5rem] whitespace-nowrap rounded-sm text-xs font-semibold uppercase tracking-wider px-2 py-1 border';
        $pillColor = [
            'green' => 'bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300 border-green-200 dark:border-green-700',
            'red'   => 'bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 border-red-200 dark:border-red-700',
            'amber' => 'bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-700',
            'blue'  => 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-700',
            'sky'   => 'bg-sky-50 dark:bg-sky-900/30 text-sky-700 dark:text-sky-300 border-sky-200 dark:border-sky-700',
            'stone' => 'bg-stone-100 dark:bg-slate-800 text-stone-700 dark:text-stone-300 border-stone-300 dark:border-stone-600',
        ];
    @endphp

    {{-- ===== HEADER + FILTER (sticks to the top while the panels scroll under it) ===== --}}
    <div class="glass-sticky rounded-sm p-3 mb-4 sticky top-0 z-30 dark:text-slate-400">
        <div class="flex flex-wrap gap-x-5 gap-y-2 items-center justify-between">
            <div class="flex items-baseline gap-2.5 min-w-0">
                <span class="text-2xl sm:text-heading text-stone-900 dark:text-slate-100 truncate" title="{{ $auditorName->name }}">{{ $auditorName->name }}</span>
            </div>

            <div class="flex flex-wrap gap-2 items-center">
                @foreach ([
                    ['yearAlert', $yearAlert, 'w-28', $years],
                    ['monthAlert', $monthAlert, 'w-32', $months],
                    ['selectAction', $selectAction, 'w-40', $actionOptions],
                    ['selectStatus', $selectStatus, 'w-40', $statusOptions],
                ] as [$model, $current, $width, $options])
                    <div class="{{ $width }} relative">
                        <select wire:model.live="{{ $model }}" class="{{ $selectClass }} {{ $current !== 'all' ? 'border-stone-900 dark:border-slate-300 font-semibold' : '' }}">
                            @foreach ($options as $value => $label)
                                <option value="{{ $value }}" @selected((string) $current === (string) $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="{{ $chevron }}">
                            <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                        </svg>
                    </div>
                @endforeach

                <div class="w-40 relative">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="absolute pointer-events-none left-2.5 top-2 size-4 text-stone-400 dark:text-slate-500">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                    <input
                        wire:model.live.debounce.300ms="searchId"
                        class="w-full bg-white dark:bg-slate-800 border border-stone-300 dark:border-slate-600 text-stone-900 dark:text-slate-100 rounded-sm pl-8 pr-7 py-1.5 text-sm focus:outline-none transition-none {{ $hasSearch ? 'border-stone-900 dark:border-slate-300 font-semibold' : '' }}"
                        placeholder="Alert ID"
                    >
                    @if ($hasSearch)
                        <button wire:click="$set('searchId', '')" title="Clear search"
                            class="absolute right-2 top-1.5 size-5 flex items-center justify-center text-stone-400 hover:text-stone-900 dark:hover:text-slate-200 cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4">
                                <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                            </svg>
                        </button>
                    @endif
                </div>

                {{-- always rendered so the row never reflows when a filter is set --}}
                <button
                    wire:click="resetScope"
                    @disabled(! $isScoped)
                    class="py-1.5 px-3 text-sm font-semibold rounded-sm border transition-none {{ $isScoped
                        ? 'border-stone-300 dark:border-slate-600 text-stone-700 dark:text-slate-300 hover:bg-stone-100 dark:hover:bg-slate-800 cursor-pointer'
                        : 'border-transparent text-stone-300 dark:text-slate-700 cursor-default' }}"
                >Reset</button>
            </div>
        </div>

        @if ($hasSearch || $selectStatus !== 'all' || $selectAction !== 'all')
            <div class="text-xs text-stone-500 dark:text-slate-400 mt-2">
                @php
                    $crumbs = [];
                    if ($hasSearch) { $crumbs[] = 'Alert ID “' . $searchId . '”'; }
                    if ($selectAction !== 'all') { $crumbs[] = $actionOptions[$selectAction] ?? $selectAction; }
                    if ($selectStatus !== 'all') { $crumbs[] = $statusOptions[$selectStatus] ?? $selectStatus; }
                @endphp
                {{ implode(' · ', $crumbs) }}
            </div>
        @endif
    </div>

    {{-- ===== KEY FIGURES + ACTIVITY TREND ===== --}}
    <div class="grid lg:grid-cols-12 gap-4 mb-4">
        <div class="glass rounded-sm p-4 lg:col-span-4 z-20 relative dark:text-slate-400">
            <div class="text-label text-stone-600 dark:text-slate-400 mb-3">Key Figures</div>
            <div class="grid grid-cols-2 gap-x-4 gap-y-4">
                @foreach ([
                    ['Total actions', number_format($figures->actions), 'text-stone-900 dark:text-slate-100', 'log entries'],
                    ['Alerts touched', number_format($figures->alerts), 'text-stone-900 dark:text-slate-100', 'distinct alert IDs'],
                    ['Audits', number_format($figures->audits), 'text-green-700 dark:text-green-400', 'ngapain auditing'],
                    ['Rework requests', number_format($figures->rework), 'text-sky-700 dark:text-sky-400', 're-export + re-classification'],
                ] as [$label, $value, $tone, $note])
                    <div class="border-l-2 border-stone-300 dark:border-slate-700 pl-3">
                        <div class="text-label text-stone-500 dark:text-slate-400 mb-1">{{ $label }}</div>
                        <div class="text-stat {{ $tone }}">{{ $value }}</div>
                        <div class="text-xs text-stone-500 dark:text-slate-400 mt-1">{{ $note }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="glass rounded-sm p-4 lg:col-span-8 z-20 relative dark:text-slate-400">
            <div class="flex flex-wrap gap-2 items-center justify-between mb-3">
                <div class="text-label text-stone-600 dark:text-slate-400">
                    Activity by {{ $chart['unit'] === 'month' ? 'Month' : 'Year' }}
                </div>
                <div class="text-xs text-stone-500 dark:text-slate-400">Click a bar to filter</div>
            </div>

            @if ($chartMax > 0)
                <div class="flex items-end gap-1.5 h-32">
                    @foreach ($chart['values'] as $i => $v)
                        @php
                            $key = $chart['keys'][$i];
                            $active = $chart['unit'] === 'month'
                                ? (string) $monthAlert === (string) $key
                                : (string) $yearAlert === (string) $key;
                        @endphp
                        <button
                            type="button"
                            wire:click="$set('{{ $chart['unit'] === 'month' ? 'monthAlert' : 'yearAlert' }}', '{{ $key }}')"
                            title="{{ $chart['labels'][$i] }} — {{ number_format($v) }} actions"
                            class="flex-1 h-full flex flex-col items-center justify-end gap-1 cursor-pointer focus:outline-none group"
                        >
                            <span class="text-xs text-stone-600 dark:text-slate-400">{{ $v > 0 ? number_format($v) : '' }}</span>
                            <div
                                class="w-full rounded-sm group-hover:opacity-70 transition-none {{ $active ? 'bg-green-700 dark:bg-green-400' : 'bg-stone-900 dark:bg-slate-200' }}"
                                style="height: {{ max(2, (int) round($v / $chartMax * 100)) }}%"
                            ></div>
                        </button>
                    @endforeach
                </div>
                <div class="flex gap-1.5 mt-1.5">
                    @foreach ($chart['labels'] as $label)
                        <div class="flex-1 text-center text-label text-stone-500 dark:text-slate-400 truncate">{{ $label }}</div>
                    @endforeach
                </div>
            @else
                <div class="h-32 flex items-center justify-center text-sm text-stone-500 dark:text-slate-400">No activity in this period</div>
            @endif
        </div>
    </div>

    {{-- ===== ALERTS BY VALIDATOR (stacked composition per validator) ===== --}}
    <div class="{{ $card }}">
        <div class="flex flex-wrap gap-2 items-baseline justify-between mb-3">
            <div class="text-label text-stone-600 dark:text-slate-400">Alerts by Validator</div>
            <div class="text-xs text-stone-500 dark:text-slate-400">Click a status or segment to filter</div>
        </div>

        {{-- legend: identity is never colour-alone, every segment is named here --}}
        <div class="flex flex-wrap gap-x-4 gap-y-1.5 mb-4 pb-3 border-b border-stone-200 dark:border-slate-800">
            @foreach ($statuses as $key => [$label, $tone])
                @php $n = $count($key); @endphp
                @continue($n === 0)
                <button type="button" wire:click="filterByStatus('{{ $key }}')"
                    aria-pressed="{{ $selectStatus === $key ? 'true' : 'false' }}"
                    class="flex items-center gap-1.5 cursor-pointer rounded-sm px-1 -mx-1 hover:bg-stone-100 dark:hover:bg-slate-800 {{ $selectStatus === $key ? 'bg-stone-100 dark:bg-slate-800' : '' }}">
                    <span class="size-2.5 rounded-[2px] shrink-0" style="background: var(--st-{{ $key }})"></span>
                    <span class="text-xs {{ $selectStatus === $key ? 'font-semibold text-stone-900 dark:text-slate-100' : 'text-stone-600 dark:text-slate-400' }}">{{ $label }}</span>
                    <span class="text-xs font-semibold text-stone-900 dark:text-slate-200">{{ number_format($n) }}</span>
                </button>
            @endforeach
        </div>

        @if ($total > 0)
            <div class="space-y-2.5">
                @foreach ($matrix['validators'] as $validator)
                    @php $validatorTotal = $matrix['totals'][$validator]; @endphp
                    <div class="flex items-center gap-3">
                        <div class="w-36 shrink-0 truncate text-xs text-stone-700 dark:text-slate-300" title="{{ $validator }}">{{ $validator }}</div>

                        {{-- full-width bar: segments are each status's share of THIS validator --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex gap-[2px] h-5 w-full">
                                @foreach ($statuses as $key => [$label, $tone])
                                    @php $n = $matrix['cells'][$key][$validator] ?? 0; @endphp
                                    @continue($n === 0)
                                    <button type="button"
                                        wire:click="filterByStatus('{{ $key }}')"
                                        title="{{ $validator }} — {{ $label }}: {{ number_format($n) }} ({{ round($n / $validatorTotal * 100) }}%)"
                                        class="h-full min-w-[3px] first:rounded-l-[3px] last:rounded-r-[3px] cursor-pointer hover:opacity-75 {{ $selectStatus !== 'all' && $selectStatus !== $key ? 'opacity-25' : '' }}"
                                        style="width: {{ round($n / $validatorTotal * 100, 3) }}%; background: var(--st-{{ $key }})"
                                    ></button>
                                @endforeach
                            </div>
                        </div>

                        <div class="w-14 shrink-0 text-right text-xs font-semibold text-stone-900 dark:text-slate-200">{{ number_format($validatorTotal) }}</div>
                    </div>
                @endforeach
            </div>

            <div class="flex items-center gap-3 mt-3 pt-3 border-t border-stone-200 dark:border-slate-800">
                <div class="w-36 shrink-0 text-xs font-semibold text-stone-900 dark:text-slate-200">All validators</div>
                <div class="flex-1 min-w-0">
                    <div class="flex gap-[2px] h-5 w-full">
                        @foreach ($statuses as $key => [$label, $tone])
                            @php $n = $count($key); @endphp
                            @continue($n === 0)
                            <button type="button"
                                wire:click="filterByStatus('{{ $key }}')"
                                title="{{ $label }}: {{ number_format($n) }} ({{ round($n / $total * 100) }}% of touched alerts)"
                                class="h-full min-w-[3px] first:rounded-l-[3px] last:rounded-r-[3px] cursor-pointer hover:opacity-75 {{ $selectStatus !== 'all' && $selectStatus !== $key ? 'opacity-25' : '' }}"
                                style="width: {{ round($n / $total * 100, 3) }}%; background: var(--st-{{ $key }})"
                            ></button>
                        @endforeach
                    </div>
                </div>
                <div class="w-14 shrink-0 text-right text-xs font-bold text-stone-900 dark:text-slate-200">{{ number_format($total) }}</div>
            </div>
        @else
            <div class="py-6 text-center text-sm text-stone-500 dark:text-slate-400">No alerts touched in this period</div>
        @endif
    </div>

    {{-- ===== AKTIVITAS ===== --}}
    <div class="{{ $card }}">
        <div class="flex flex-wrap gap-2 items-center justify-between mb-3">
            <div class="text-label text-stone-600 dark:text-slate-400">Aktivitas</div>
            <div class="text-xs text-stone-500 dark:text-slate-400">Work logged by this auditor</div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-max border border-stone-200 dark:border-slate-700 text-xs">
                <thead>
                    <tr class="border-b border-stone-200 dark:border-slate-700">
                        <th class="sticky left-0 min-w-56 bg-stone-100 dark:bg-slate-800 text-left px-3 py-2 text-label text-stone-500 dark:text-slate-400 z-10 border-b border-stone-200 dark:border-slate-700">
                            Action
                        </th>
                        @foreach ($activity['periods'] as $p)
                            <th class="w-24 px-4 py-2 text-center whitespace-nowrap bg-stone-200 dark:bg-slate-700 dark:text-slate-400 border-l border-r border-stone-300 dark:border-slate-700">
                                {{ $activity['labels'][$p] }}
                            </th>
                        @endforeach
                        <th class="w-24 sticky right-0 bg-stone-400 dark:bg-slate-700 text-center px-3 py-2 text-label text-stone-700 dark:text-slate-300 z-10 border-b border-stone-400 dark:border-slate-700">
                            Total
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-stone-200 dark:divide-slate-700">
                    @forelse ($activity['actions'] as $action)
                        <tr>
                            <td class="sticky left-0 bg-white dark:bg-slate-900 px-3 py-2 z-10 whitespace-nowrap font-medium text-stone-800 dark:text-slate-200 border-b border-stone-200 dark:border-slate-700">
                                {{ $action['label'] }}
                            </td>
                            @foreach ($activity['periods'] as $p)
                                <td class="px-4 py-2 text-center bg-stone-200 dark:bg-slate-700 dark:text-slate-300 border-l border-r border-b border-stone-300 dark:border-slate-700">
                                    {{ $action['periods'][$p] ?? 0 }}
                                </td>
                            @endforeach
                            <td class="w-24 sticky right-0 bg-stone-400 dark:bg-slate-700 px-4 py-2 text-center font-semibold z-10 dark:text-slate-300 border-b border-stone-400 dark:border-slate-700">
                                {{ number_format($action['total']) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($activity['periods']) + 2 }}" class="px-3 py-6 text-center text-sm text-stone-500 dark:text-slate-400">
                                No activity in this period
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                @if (!empty($activity['actions']))
                    <tfoot>
                        <tr>
                            <td class="sticky left-0 bg-stone-100 dark:bg-slate-800 px-3 py-2 z-10 font-semibold text-stone-900 dark:text-slate-200">Total</td>
                            @foreach ($activity['periods'] as $p)
                                <td class="px-4 py-2 text-center font-semibold bg-stone-300 dark:bg-slate-600 dark:text-slate-200 border-l border-r border-stone-300 dark:border-slate-700">
                                    {{ number_format($activity['columnTotals'][$p]) }}
                                </td>
                            @endforeach
                            <td class="w-24 sticky right-0 bg-stone-400 dark:bg-slate-700 px-4 py-2 text-center font-bold z-10 dark:text-slate-200">
                                {{ number_format($activity['grandTotal']) }}
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- ===== AUDIT LOG ===== --}}
    <div class="{{ $card }}">
        <div class="flex flex-wrap gap-2 items-center justify-between mb-3">
            <div class="text-label text-stone-600 dark:text-slate-400">Audit Log</div>
            @if ($logs->count() > 0)
                <div class="text-xs text-stone-500 dark:text-slate-400">
                    {{ $logs->firstItem() }}–{{ $logs->lastItem() }} of {{ number_format($logs->total()) }}
                </div>
            @endif
        </div>

        <div wire:loading class="w-full bg-stone-900 dark:bg-slate-200 h-0.5 animate-pulse rounded-sm mb-2"></div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-xs">
                <thead>
                    <tr class="border-b border-stone-300 dark:border-slate-700">
                        <th class="text-left px-3 py-1.5 text-label text-stone-500 dark:text-slate-400">Audited at</th>
                        <th class="text-left px-3 py-1.5 text-label text-stone-500 dark:text-slate-400">Alert ID</th>
                        <th class="text-left px-3 py-1.5 text-label text-stone-500 dark:text-slate-400">Validator</th>
                        <th class="text-left px-3 py-1.5 text-label text-stone-500 dark:text-slate-400 hidden sm:table-cell">Region</th>
                        <th class="text-left px-3 py-1.5 text-label text-stone-500 dark:text-slate-400 hidden sm:table-cell">Province</th>
                        <th class="text-center px-3 py-1.5 text-label text-stone-500 dark:text-slate-400">Action</th>
                        <th class="text-center px-3 py-1.5 text-label text-stone-500 dark:text-slate-400">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-200 dark:divide-slate-700">
                    @forelse ($logs as $item)
                        @php
                            $key = strtolower($item->auditorStatus ?: 'pending');
                            [$label, $color] = $statuses[$key] ?? [ucfirst($key), 'stone'];
                        @endphp
                        <tr wire:key="log-{{ $item->alertId }}-{{ $item->auditedAt }}">
                            <td class="px-3 py-1.5 text-stone-700 dark:text-slate-300 whitespace-nowrap">{{ \Carbon\Carbon::parse($item->auditedAt)->format('d-m-Y H:i') }}</td>
                            <td class="px-3 py-1.5 text-stone-700 dark:text-slate-300">{{ $item->alertId }}</td>
                            <td class="px-3 py-1.5 text-stone-700 dark:text-slate-300 max-w-36 truncate" title="{{ $item->validator }}">{{ $item->validator }}</td>
                            <td class="px-3 py-1.5 text-stone-700 dark:text-slate-300 hidden sm:table-cell">{{ $item->region }}</td>
                            <td class="px-3 py-1.5 text-stone-700 dark:text-slate-300 hidden sm:table-cell">{{ $item->province }}</td>
                            <td class="px-3 py-1.5 text-center">
                                <div class="{{ $pill }} {{ $pillColor['stone'] }}">{{ ucfirst($item->ngapain) }}</div>
                            </td>
                            <td class="px-3 py-1.5 text-center">
                                <div class="{{ $pill }} {{ $pillColor[$color] }}">{{ $label }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-3 py-8 text-center">
                                <div class="text-sm text-stone-500 dark:text-slate-400">No audit log entries match this filter.</div>
                                @if ($isScoped)
                                    <button wire:click="resetScope" class="mt-3 {{ $ghostBtn }}">Reset</button>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $logs->links('livewire.pagination') }}
        </div>
    </div>
</div>
