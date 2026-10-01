<div class="dark:text-slate-400">
    @php
        $roles = [
            2 => 'Validator',
            1 => 'Auditor',
            0 => 'Admin',
        ];
        $total = (int) $roleStats->sum('total');
        $hasSearch = $search !== null && $search !== '';
        $isScoped = $hasSearch || $selectRole !== 'all' || $selectActive !== 'all';

        $th = 'px-3 py-1.5 text-label text-stone-500 dark:text-slate-400 whitespace-nowrap';
        $td = 'px-3 py-2 text-stone-700 dark:text-slate-300';
        $chip = 'flex items-center gap-1.5 cursor-pointer rounded-sm px-1 -mx-1 hover:bg-stone-100 dark:hover:bg-slate-800';
        $iconBtn = 'p-1 rounded-sm text-stone-600 dark:text-slate-400 hover:bg-stone-200 hover:text-stone-900 dark:hover:bg-slate-700 dark:hover:text-slate-100 cursor-pointer';
        $link = 'text-green-700 dark:text-accent hover:underline';
    @endphp

    {{-- ===== HEADER + FILTER ===== --}}
    <div class="glass-sticky rounded-sm p-3 mb-4 sticky top-0 z-30 dark:text-slate-400">
        <div class="flex flex-wrap gap-x-5 gap-y-2 items-center justify-between">
            <div class="flex items-baseline gap-2.5">
                <span class="text-2xl sm:text-heading text-stone-900 dark:text-slate-100">Users</span>
                <span class="text-sm text-stone-500 dark:text-slate-400 tabular-nums">{{ number_format($databases->total()) }}</span>
            </div>

            <div class="flex flex-wrap gap-2 items-center">
                <div class="flex rounded-sm border border-stone-300 dark:border-slate-600 overflow-hidden text-sm">
                    @foreach (['all' => 'All', '1' => 'Active', '0' => 'Inactive'] as $value => $label)
                        <button type="button" wire:click="$set('selectActive', '{{ $value }}')" aria-pressed="{{ (string) $selectActive === (string) $value ? 'true' : 'false' }}"
                            class="px-3 py-1.5 cursor-pointer transition-none {{ (string) $selectActive === (string) $value
                                ? 'bg-stone-900 dark:bg-slate-200 text-white dark:text-stone-900 font-semibold'
                                : 'bg-white dark:bg-slate-800 text-stone-700 dark:text-slate-300 hover:bg-stone-100 dark:hover:bg-slate-700' }}">{{ $label }}</button>
                    @endforeach
                </div>

                <div class="w-52 relative">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="absolute pointer-events-none left-2.5 top-2 size-4 text-stone-400 dark:text-slate-500">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                    <input
                        wire:model.live.debounce.300ms="search"
                        class="w-full bg-white dark:bg-slate-800 border border-stone-300 dark:border-slate-600 text-stone-900 dark:text-slate-100 rounded-sm pl-8 pr-7 py-1.5 text-sm focus:outline-none transition-none {{ $hasSearch ? 'border-stone-900 dark:border-slate-300 font-semibold' : '' }}"
                        placeholder="Name or email"
                    >
                    @if ($hasSearch)
                        <button wire:click="$set('search', '')" title="Clear search"
                            class="absolute right-2 top-1.5 size-5 flex items-center justify-center text-stone-400 hover:text-stone-900 dark:hover:text-slate-200 cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4">
                                <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                            </svg>
                        </button>
                    @endif
                </div>

                <button
                    wire:click="resetScope"
                    @disabled(! $isScoped)
                    class="py-1.5 px-3 text-sm font-semibold rounded-sm border transition-none {{ $isScoped
                        ? 'border-stone-300 dark:border-slate-600 text-stone-700 dark:text-slate-300 hover:bg-stone-100 dark:hover:bg-slate-800 cursor-pointer'
                        : 'border-transparent text-stone-300 dark:text-slate-700 cursor-default' }}"
                >Reset</button>

                <a href="{{ url('/adduser') }}"
                    class="flex items-center gap-1.5 bg-stone-900 dark:bg-slate-200 text-white dark:text-stone-900 py-1.5 px-3 text-sm font-semibold rounded-sm cursor-pointer hover:bg-stone-800 dark:hover:bg-slate-300 transition-none">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4">
                        <path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z" />
                    </svg>
                    Add user
                </a>
            </div>
        </div>

        {{-- role legend doubles as the role filter; counts follow the search --}}
        <div class="flex flex-wrap gap-x-4 gap-y-1.5 mt-3 pt-3 border-t border-stone-200 dark:border-slate-800">
            <button type="button" wire:click="$set('selectRole', 'all')" aria-pressed="{{ $selectRole === 'all' ? 'true' : 'false' }}"
                class="{{ $chip }} {{ $selectRole === 'all' ? 'bg-stone-100 dark:bg-slate-800' : '' }}">
                <span class="text-xs {{ $selectRole === 'all' ? 'font-semibold text-stone-900 dark:text-slate-100' : 'text-stone-600 dark:text-slate-400' }}">All</span>
                <span class="text-xs font-semibold text-stone-900 dark:text-slate-200 tabular-nums">{{ number_format($total) }}</span>
            </button>
            @foreach ($roles as $id => $label)
                @php
                    $stat = $roleStats[$id] ?? null;
                    $on = (string) $selectRole === (string) $id;
                @endphp
                <button type="button" wire:click="$set('selectRole', '{{ $on ? 'all' : $id }}')" aria-pressed="{{ $on ? 'true' : 'false' }}"
                    class="{{ $chip }} {{ $on ? 'bg-stone-100 dark:bg-slate-800' : '' }}">
                    <span class="text-xs {{ $on ? 'font-semibold text-stone-900 dark:text-slate-100' : 'text-stone-600 dark:text-slate-400' }}">{{ $label }}s</span>
                    <span class="text-xs font-semibold text-stone-900 dark:text-slate-200 tabular-nums">{{ number_format($stat->total ?? 0) }}</span>
                    @if ($stat && $stat->active < $stat->total)
                        <span class="text-xs text-stone-400 dark:text-slate-500 tabular-nums">({{ $stat->total - $stat->active }} inactive)</span>
                    @endif
                </button>
            @endforeach
        </div>
    </div>

    @include('partials.deleterModal')

    {{-- ===== TABLE ===== --}}
    <div class="glass rounded-sm p-4 mb-4 z-20 relative dark:text-slate-400">
        <div wire:loading.delay class="absolute top-0 left-0 right-0 h-0.5 bg-stone-900 dark:bg-slate-200 animate-pulse rounded-t-sm"></div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-xs">
                <thead>
                    <tr class="border-b border-stone-300 dark:border-slate-700">
                        @foreach ([
                            ['name', 'User', 'text-left'],
                            ['role_id', 'Role', 'text-left'],
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
                        <th class="{{ $th }} text-left hidden md:table-cell">Contact</th>
                        <th class="{{ $th }} text-right hidden sm:table-cell">Work</th>
                        <th class="{{ $th }} text-left hidden sm:table-cell" aria-sort="{{ $dataField === 'last_active' ? ($dataOrder === 'asc' ? 'ascending' : 'descending') : 'none' }}">
                            <button type="button" wire:click="sortingField('last_active')" class="inline-flex items-center gap-1 text-label cursor-pointer hover:text-stone-900 dark:hover:text-slate-200 {{ $dataField === 'last_active' ? 'text-stone-900 dark:text-slate-100' : '' }}">
                                Last active
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-3.5 {{ $dataField === 'last_active' ? '' : 'opacity-30' }} {{ $dataField === 'last_active' && $dataOrder === 'desc' ? 'rotate-180' : '' }}">
                                    <path fill-rule="evenodd" d="M10 17a.75.75 0 0 1-.75-.75V5.612L5.29 9.77a.75.75 0 0 1-1.08-1.04l5.25-5.5a.75.75 0 0 1 1.08 0l5.25 5.5a.75.75 0 1 1-1.08 1.04l-3.96-4.158V16.25A.75.75 0 0 1 10 17Z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </th>
                        <th class="{{ $th }} text-left">Status</th>
                        <th class="{{ $th }} w-24"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-stone-200 dark:divide-slate-700">
                    @forelse ($databases as $item)
                        @php
                            $isMe = (int) $item->id === (int) session('id');
                            $active = (int) $item->is_active === 1;
                            $initials = collect(preg_split('/\s+/', trim($item->name)))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
                            // server re-checks every row the user owns; this only decides whether to offer it
                            $deletable = ! $isMe && (int) $item->alerts_count === 0 && $item->last_work === null;
                        @endphp
                        <tr wire:key="user-{{ $item->id }}" class="hover:bg-stone-50 dark:hover:bg-slate-800/60 {{ $active ? '' : 'opacity-60' }}">
                            <td class="{{ $td }}">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <span class="shrink-0 size-7 rounded-full bg-stone-200 dark:bg-slate-600 flex items-center justify-center text-[10px] font-semibold text-stone-700 dark:text-slate-300">{{ $initials }}</span>
                                    <div class="min-w-0">
                                        <a href="{{ url('/edituser/'.$item->id) }}" class="block truncate font-semibold text-stone-900 dark:text-slate-100 hover:underline">
                                            {{ $item->name }}
                                            @if ($isMe)
                                                <span class="ml-1 text-label text-stone-400 dark:text-slate-500 font-semibold">You</span>
                                            @endif
                                        </a>
                                        <div class="truncate text-stone-500 dark:text-slate-400">{{ $item->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="{{ $td }}">
                                <span class="inline-flex rounded-sm border border-stone-300 dark:border-slate-600 bg-stone-100 dark:bg-slate-800 px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wider text-stone-700 dark:text-slate-300">{{ $roles[$item->role_id] ?? 'Unknown' }}</span>
                            </td>
                            <td class="{{ $td }} hidden md:table-cell tabular-nums">{{ $item->contact }}</td>
                            <td class="{{ $td }} text-right whitespace-nowrap tabular-nums hidden sm:table-cell">
                                @if ((int) $item->role_id === 2)
                                    <a href="{{ url('/alertanalis/'.$item->id) }}" class="{{ $link }}" title="Open validator monitor">{{ number_format($item->alerts_count) }} alerts</a>
                                @elseif ((int) $item->role_id === 1)
                                    <a href="{{ url('/auditor-alert/'.$item->id) }}" class="{{ $link }}" title="Open auditor activity">{{ number_format($item->audits_count) }} audits</a>
                                @else
                                    <span class="text-stone-400 dark:text-slate-500">—</span>
                                @endif
                            </td>
                            <td class="{{ $td }} whitespace-nowrap hidden sm:table-cell">
                                @if ($item->last_active)
                                    <span title="{{ \Carbon\Carbon::parse($item->last_active)->format('d-m-Y H:i') }}">{{ \Carbon\Carbon::parse($item->last_active)->diffForHumans() }}</span>
                                @else
                                    <span class="text-stone-400 dark:text-slate-500">Never</span>
                                @endif
                            </td>
                            <td class="{{ $td }} whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5">
                                    <span class="size-2 rounded-full {{ $active ? 'bg-green-700 dark:bg-green-400' : 'bg-stone-400 dark:bg-slate-500' }}"></span>
                                    {{ $active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-3 py-2">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ url('/edituser/'.$item->id) }}" title="Edit user" aria-label="Edit {{ $item->name }}" class="{{ $iconBtn }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                        </svg>
                                    </a>
                                    @unless ($isMe)
                                        <button type="button" wire:click="toggleActive({{ $item->id }})"
                                            @if ($active) wire:confirm="Deactivate {{ $item->name }}? They will no longer be able to sign in, and their alerts drop out of the alert lists until reactivated." @endif
                                            title="{{ $active ? 'Deactivate' : 'Activate' }}" aria-label="{{ $active ? 'Deactivate' : 'Activate' }} {{ $item->name }}" class="{{ $iconBtn }}">
                                            @if ($active)
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                                                </svg>
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                </svg>
                                            @endif
                                        </button>
                                    @endunless
                                    @if ($deletable)
                                        <button type="button" wire:click="delete({{ $item->id }})" title="Delete user" aria-label="Delete {{ $item->name }}"
                                            class="p-1 rounded-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 cursor-pointer">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-3 py-8 text-center">
                                <div class="text-sm text-stone-500 dark:text-slate-400">No users match this filter.</div>
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
