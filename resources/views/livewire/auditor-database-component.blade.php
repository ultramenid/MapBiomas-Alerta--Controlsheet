<div class="dark:text-slate-400">
    @php
        // Canonical status order + house colour family, same map as the auditor page.
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
        $terminal = ['approved', 'rejected', 'duplicate'];

        $role = session('role_id');
        $total = (int) $statusStats->sum();
        $hasSearch = $searchId !== null && $searchId !== '';
        $isScoped = $hasSearch || $selectStatus !== 'all' || $yearAlert !== 'all' || $selectOwner !== 'all';
        $years = ['all' => 'All Years'] + collect(range((int) date('Y'), 2020))->mapWithKeys(fn ($y) => [$y => $y])->all();

        $selectClass = 'w-full appearance-none bg-white dark:bg-slate-800 border border-stone-300 dark:border-slate-600 text-stone-900 dark:text-slate-100 px-3 py-1.5 text-sm rounded-sm focus:outline-none cursor-pointer transition-none';
        $th = 'px-3 py-1.5 text-label text-stone-500 dark:text-slate-400 whitespace-nowrap';
        $td = 'px-3 py-1.5 text-stone-700 dark:text-slate-300';
        $pill = 'inline-flex items-center justify-center text-center w-[9.5rem] whitespace-nowrap rounded-sm text-xs font-semibold uppercase tracking-wider px-2 py-1 border';
    @endphp

    {{-- ===== HEADER + FILTER (sticks to the top while the table scrolls under it) ===== --}}
    <div class="glass-sticky rounded-sm p-3 mb-4 sticky top-0 z-30 dark:text-slate-400">
        <div class="flex flex-wrap gap-x-5 gap-y-2 items-center justify-between">
            <div class="flex items-baseline gap-2.5">
                <span class="text-2xl sm:text-heading text-stone-900 dark:text-slate-100">Alerts</span>
                <span class="text-sm text-stone-500 dark:text-slate-400 tabular-nums">{{ number_format($databases->total()) }}</span>
            </div>

            <div class="flex flex-wrap gap-2 items-center">
                {{-- segmented: everyone's alerts or only the ones you input --}}
                    <div class="flex rounded-sm border border-stone-300 dark:border-slate-600 overflow-hidden text-sm">
                        @foreach (['all' => 'All', 'mine' => 'Mine'] as $value => $label)
                            <button type="button" wire:click="$set('selectOwner', '{{ $value }}')" aria-pressed="{{ $selectOwner === $value ? 'true' : 'false' }}"
                                class="px-3 py-1.5 cursor-pointer transition-none {{ $selectOwner === $value
                                    ? 'bg-stone-900 dark:bg-slate-200 text-white dark:text-stone-900 font-semibold'
                                    : 'bg-white dark:bg-slate-800 text-stone-700 dark:text-slate-300 hover:bg-stone-100 dark:hover:bg-slate-700' }}">{{ $label }}</button>
                        @endforeach
                    </div>

                <div class="w-28 relative">
                    <select wire:model.live="yearAlert" class="{{ $selectClass }} {{ $yearAlert !== 'all' ? 'border-stone-900 dark:border-slate-300 font-semibold' : '' }}">
                        @foreach ($years as $value => $label)
                            <option value="{{ $value }}" @selected((string) $yearAlert === (string) $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="absolute pointer-events-none right-3 top-2 size-4 text-stone-500">
                        <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                    </svg>
                </div>

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

        {{-- status legend doubles as the status filter; counts follow the year/owner/search scope --}}
        <div class="flex flex-wrap gap-x-4 gap-y-1.5 mt-3 pt-3 border-t border-stone-200 dark:border-slate-800">
            <button type="button" wire:click="filterStatus('all')" aria-pressed="{{ $selectStatus === 'all' ? 'true' : 'false' }}"
                class="flex items-center gap-1.5 cursor-pointer rounded-sm px-1 -mx-1 hover:bg-stone-100 dark:hover:bg-slate-800 {{ $selectStatus === 'all' ? 'bg-stone-100 dark:bg-slate-800' : '' }}">
                <span class="text-xs {{ $selectStatus === 'all' ? 'font-semibold text-stone-900 dark:text-slate-100' : 'text-stone-600 dark:text-slate-400' }}">All</span>
                <span class="text-xs font-semibold text-stone-900 dark:text-slate-200 tabular-nums">{{ number_format($total) }}</span>
            </button>
            @foreach ($statuses as $key => [$label, $tone])
                @php $n = (int) ($statusStats[$key] ?? 0); @endphp
                @continue($n === 0 && $selectStatus !== $key)
                <button type="button" wire:click="filterStatus('{{ $key }}')" aria-pressed="{{ $selectStatus === $key ? 'true' : 'false' }}"
                    class="flex items-center gap-1.5 cursor-pointer rounded-sm px-1 -mx-1 hover:bg-stone-100 dark:hover:bg-slate-800 {{ $selectStatus === $key ? 'bg-stone-100 dark:bg-slate-800' : '' }}">
                    <span class="size-2.5 rounded-[2px] shrink-0" style="background: var(--st-{{ $key }})"></span>
                    <span class="text-xs {{ $selectStatus === $key ? 'font-semibold text-stone-900 dark:text-slate-100' : 'text-stone-600 dark:text-slate-400' }}">{{ $label }}</span>
                    <span class="text-xs font-semibold text-stone-900 dark:text-slate-200 tabular-nums">{{ number_format($n) }}</span>
                </button>
            @endforeach
        </div>
    </div>

    @include('partials.auditing')
    @include('partials.deleterAlert')

    {{-- ===== TABLE ===== --}}
    <div class="glass rounded-sm p-4 mb-4 z-20 relative dark:text-slate-400">
        <div wire:loading.delay wire:target="searchId, selectStatus, filterStatus, yearAlert, selectOwner, resetScope, sortingField, previousPage, nextPage, gotoPage"
            class="absolute top-0 left-0 right-0 h-0.5 bg-stone-900 dark:bg-slate-200 animate-pulse rounded-t-sm"></div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-xs">
                <thead>
                    <tr class="border-b border-stone-300 dark:border-slate-700">
                        @foreach ([
                            ['alertId', 'Alert ID', 'text-left'],
                            ['detectionDate', 'Detected', 'text-left'],
                            ['created_at', 'Input', 'text-left hidden sm:table-cell'],
                        ] as [$field, $label, $class])
                            <th class="{{ $th }} {{ $class }}" aria-sort="{{ $dataField === $field ? ($dataOrder === 'asc' ? 'ascending' : 'descending') : 'none' }}">
                                <button type="button" wire:click="sortingField('{{ $field }}')" class="inline-flex items-center gap-1 text-label cursor-pointer hover:text-stone-900 dark:hover:text-slate-200 {{ $dataField === $field ? 'text-stone-900 dark:text-slate-100' : '' }}">
                                    {{ $label }}
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-3.5 {{ $dataField === $field ? '' : 'opacity-30' }} {{ $dataField === $field && $dataOrder === 'desc' ? 'rotate-180' : '' }}">
                                        <path fill-rule="evenodd" d="M10 17a.75.75 0 0 1-.75-.75V5.612L5.29 9.77a.75.75 0 0 1-1.08-1.04l5.25-5.5a.75.75 0 0 1 1.08 0l5.25 5.5a.75.75 0 1 1-1.08 1.04l-3.96-4.158V16.25A.75.75 0 0 1 10 17Z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </th>
                        @endforeach
                        @if ($selectOwner !== 'mine')
                            <th class="{{ $th }} text-left hidden md:table-cell">Validator</th>
                        @endif
                        <th class="{{ $th }} text-left hidden sm:table-cell">Location</th>
                        <th class="{{ $th }} text-center" aria-sort="{{ $dataField === 'auditorStatus' ? ($dataOrder === 'asc' ? 'ascending' : 'descending') : 'none' }}">
                            <button type="button" wire:click="sortingField('auditorStatus')" class="inline-flex items-center gap-1 text-label cursor-pointer hover:text-stone-900 dark:hover:text-slate-200 {{ $dataField === 'auditorStatus' ? 'text-stone-900 dark:text-slate-100' : '' }}">
                                Status
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-3.5 {{ $dataField === 'auditorStatus' ? '' : 'opacity-30' }} {{ $dataField === 'auditorStatus' && $dataOrder === 'desc' ? 'rotate-180' : '' }}">
                                    <path fill-rule="evenodd" d="M10 17a.75.75 0 0 1-.75-.75V5.612L5.29 9.77a.75.75 0 0 1-1.08-1.04l5.25-5.5a.75.75 0 0 1 1.08 0l5.25 5.5a.75.75 0 1 1-1.08 1.04l-3.96-4.158V16.25A.75.75 0 0 1 10 17Z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </th>
                        <th class="{{ $th }} w-16"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-stone-200 dark:divide-slate-700">
                    @forelse ($databases as $item)
                        @php
                            $key = strtolower($item->auditorStatus ?: 'pending');
                            $label = $statuses[$key][0] ?? ucfirst($key);
                            // ponytail: admin acts on any, analis on own (analisId == session id), role 1 none; terminal states are locked
                            $canManage = ! in_array($key, $terminal)
                                && ($role == 0 || ($role == 2 && $item->analisId == session('id')));
                        @endphp
                        <tr wire:key="alert-{{ $item->alertId }}" class="hover:bg-stone-50 dark:hover:bg-slate-800/60">
                            <td class="{{ $td }} font-semibold text-stone-900 dark:text-slate-100 tabular-nums">{{ $item->alertId }}</td>
                            <td class="{{ $td }} whitespace-nowrap tabular-nums">{{ \Carbon\Carbon::parse($item->detectionDate)->format('d-m-Y') }}</td>
                            <td class="{{ $td }} whitespace-nowrap tabular-nums hidden sm:table-cell" title="{{ \Carbon\Carbon::parse($item->created_at)->format('d-m-Y H:i') }}">{{ \Carbon\Carbon::parse($item->created_at)->format('d-m-Y') }}</td>
                            @if ($selectOwner !== 'mine')
                                <td class="{{ $td }} max-w-40 truncate hidden md:table-cell" title="{{ $item->validator }}">
                                    {{ $item->validator }}
                                    @if ($item->analisId == session('id'))
                                        <span class="ml-1 text-label text-stone-400 dark:text-slate-500">You</span>
                                    @endif
                                </td>
                            @endif
                            <td class="{{ $td }} hidden sm:table-cell">
                                <span>{{ $item->province }}</span>
                                <span class="text-stone-400 dark:text-slate-500">· {{ $item->region }}</span>
                            </td>
                            <td class="px-3 py-1.5 text-center">
                                @if (in_array($key, ['pre-approved', 'refined', 'error', 'pending']))
                                    {{-- ponytail: validator (role 2) changes platform status inline; auditor (1) / admin (0) open the audit dialog --}}
                                    @if ($role == 2)
                                        <div class="relative inline-flex">
                                            <select
                                                onchange="Livewire.dispatch('updateStatus', { id: '{{ $item->alertId }}', status: this.value })"
                                                class="{{ $pill }} st-pill st-{{ $key }} appearance-none cursor-pointer pr-6 focus:outline-none"
                                                style="text-align-last: center;" title="Change status">
                                                <option value="pre-approved" @selected($key == 'pre-approved')>Pre-Approved</option>
                                                <option value="refined" @selected($key == 'refined')>Refined</option>
                                                <option value="error" @selected($key == 'error')>Error</option>
                                                <option value="pending" @selected($key == 'pending')>Pending</option>
                                            </select>
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="absolute right-1.5 top-1/2 -translate-y-1/2 size-3.5 pointer-events-none opacity-70">
                                                <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    @else
                                        <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-audit-modal', { detail: { id: {{ $item->id }} } }))"
                                            class="{{ $pill }} st-pill st-{{ $key }} cursor-pointer hover:opacity-75" title="Open audit dialog">
                                            {{ $label }}
                                        </button>
                                    @endif
                                @else
                                    <span class="{{ $pill }} st-pill st-{{ $key }}">{{ $label }}</span>
                                @endif
                            </td>
                            <td class="px-3 py-1.5">
                                @if ($canManage)
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ url('/editalert/'.$item->alertId) }}" title="Edit alert" aria-label="Edit alert {{ $item->alertId }}"
                                            class="p-1 rounded-sm text-stone-600 dark:text-slate-400 hover:bg-stone-200 hover:text-stone-900 dark:hover:bg-slate-700 dark:hover:text-slate-100">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                            </svg>
                                        </a>
                                        <button type="button" wire:click="deleteAlert({{ $item->alertId }})" title="Delete alert" aria-label="Delete alert {{ $item->alertId }}"
                                            class="p-1 rounded-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 cursor-pointer">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                        </button>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-3 py-8 text-center">
                                <div class="text-sm text-stone-500 dark:text-slate-400">No alerts match this filter.</div>
                                @if ($isScoped)
                                    <button wire:click="resetScope" class="mt-3 border border-stone-300 dark:border-slate-600 text-stone-700 dark:text-slate-300 py-1.5 px-3 text-sm font-semibold rounded-sm cursor-pointer hover:bg-stone-100 dark:hover:bg-slate-800 transition-none">Reset</button>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $databases->links('livewire.pagination') }}
    </div>
</div>
