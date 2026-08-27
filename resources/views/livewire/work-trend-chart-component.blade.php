<div class="glass rounded-sm p-5 mb-5 z-20 relative dark:text-slate-400" data-wt-root>
    {{-- Header --}}
    <div class="mb-4">
        <div class="text-label text-stone-600 dark:text-slate-400">Daily Work Summary</div>
        <p class="text-xs text-stone-400 dark:text-slate-500 mt-1">
            @if ($role == 0)
                Output per day — drag the slider at the bottom to filter all breakdowns.
            @elseif ($role == 1)
                Your daily auditing output — drag the slider to filter the detail below.
            @else
                Your daily validation output — drag the slider to filter the detail below.
            @endif
        </p>
    </div>

    {{-- Alert lookup lives in its own component: typing must not re-render the
         chart, or the JS-built brush below gets morphed away mid-search. --}}
    @if ($role == 0)
        <livewire:alert-lookup-component />
    @endif

    @if (count($payload['dates']) === 0)
        <p class="text-xs text-stone-500 dark:text-slate-400 py-8 text-center">No work logged yet.</p>
    @else
        {{-- Chart area --}}
        <div wire:key="wtc-{{ $payloadKey }}" wire:ignore x-data x-init="WorkTrendChart($el)" data-payload="{{ json_encode($payload) }}">
            <div class="{{ $role == 0 ? 'grid gap-8 md:grid-cols-2' : '' }}">
                <div>
                    <div class="flex items-baseline justify-between mb-2">
                        <span class="text-label text-stone-600 dark:text-slate-400 flex items-center gap-2">
                            <span class="inline-block size-2 rounded-full bg-stone-800 dark:bg-slate-100"></span>
                            @if ($role == 0) Auditor tasks / day @elseif ($role == 1) My tasks / day @else My validations / day @endif
                        </span>
                        <span class="text-xs tabular-nums text-stone-500 dark:text-slate-400" data-total="auditor"></span>
                    </div>
                    <div class="wt-chart relative h-[210px] text-stone-800 dark:text-slate-100" data-series="auditor"></div>
                </div>
                @if ($role == 0)
                <div>
                    <div class="flex items-baseline justify-between mb-2">
                        <span class="text-label text-stone-600 dark:text-slate-400 flex items-center gap-2">
                            <span class="inline-block size-2 rounded-full bg-[#3F72AF] dark:bg-[#7BA3EE]"></span>
                            Validator tasks / day
                        </span>
                        <span class="text-xs tabular-nums text-stone-500 dark:text-slate-400" data-total="validator"></span>
                    </div>
                    <div class="wt-chart relative h-[210px]" data-series="validator"></div>
                </div>
                @endif
            </div>
        </div>

        {{-- Tables in matching columns (same grid as chart) --}}
        @if ($role == 0)
        {{-- fr-unit columns animate; col-span would snap --}}
        <div x-data="{ wide: null }"
             class="grid gap-x-8 gap-y-8 md:grid-cols-2 mt-6 pt-5 border-t border-stone-200 dark:border-slate-700 transition-[grid-template-columns,column-gap] duration-300 ease-out"
             :class="wide === 'auditor' ? 'md:grid-cols-[1fr_0fr]! md:gap-x-0!' : wide === 'validator' ? 'md:grid-cols-[0fr_1fr]! md:gap-x-0!' : ''">
            <div class="min-w-0 overflow-hidden transition-opacity duration-200"
                 :class="wide === 'validator' ? 'opacity-0 max-md:hidden md:h-0!' : 'opacity-100'">
                <livewire:auditor-summary-component />
            </div>
            <div class="min-w-0 overflow-hidden transition-opacity duration-200"
                 :class="wide === 'auditor' ? 'opacity-0 max-md:hidden md:h-0!' : 'opacity-100'">
                <livewire:validator-task-component :scopeUserId="$role === 0 ? null : session('id')" />
            </div>
        </div>
        @elseif ($role == 1)
        <div class="mt-6 pt-5 border-t border-stone-200 dark:border-slate-700">
            <livewire:auditor-task-component />
        </div>
        @elseif ($role == 2)
        <div class="mt-6 pt-5 border-t border-stone-200 dark:border-slate-700">
            <livewire:validator-task-component :scopeUserId="session('id')" />
        </div>
        @endif
    @endif

    {{-- Shared brush slider (at the bottom) --}}
    @if (count($payload['dates']) > 0)
    {{-- sticky bottom: long tables push the brush off-screen, and it is the only
         way to change the range. Bleeds to the card edges over the p-5 padding. --}}
    <div wire:ignore class="sticky bottom-0 z-40 mt-6 -mx-5 -mb-5 px-5 pt-4 pb-5 border-t border-stone-200 dark:border-slate-700 bg-white/85 dark:bg-slate-900/85 backdrop-blur-sm">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs tabular-nums text-stone-500 dark:text-slate-400" data-range-label></span>
            <button type="button" class="text-xs text-stone-500 dark:text-slate-400 underline hover:text-stone-800 dark:hover:text-slate-200 cursor-pointer" data-reset>Reset</button>
        </div>
        <div class="relative h-[46px] select-none touch-none text-stone-500 dark:text-slate-400" data-brush></div>
    </div>
    @endif
</div>