<div class="@unless($isAdmin) hidden @endunless glass rounded-sm p-5 mb-5 z-20 relative dark:text-slate-400">
    @if ($isAdmin)
    <div class="text-sm mb-6">
        <span class="text-label text-stone-600 dark:text-slate-400 mb-1">Daily Work Summary</span>
        <p class="text-xs text-stone-400 dark:text-slate-500 mt-1">Auditor & validator output per day — drag the footer slider to move or resize the time range.</p>
    </div>

    @if (count($payload['dates']) === 0)
        <p class="text-xs text-stone-500 dark:text-slate-400 py-8 text-center">No auditor or validator work logged yet.</p>
    @else
        <div wire:key="wtc-{{ $payloadKey }}" wire:ignore x-data x-init="WorkTrendChart($el)" data-payload="{{ json_encode($payload) }}">
            <div class="grid gap-8 md:grid-cols-2">
                <div>
                    <div class="flex items-baseline justify-between mb-2">
                        <span class="text-label text-stone-600 dark:text-slate-400 flex items-center gap-2">
                            <span class="inline-block size-2 rounded-full bg-stone-800 dark:bg-slate-100"></span>
                            Auditor tasks / day
                        </span>
                        <span class="text-xs tabular-nums text-stone-500 dark:text-slate-400" data-total="auditor"></span>
                    </div>
                    <div class="wt-chart relative h-[210px] text-stone-800 dark:text-slate-100" data-series="auditor"></div>
                </div>
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
            </div>

            <div class="mt-5 pt-4 border-t border-stone-200 dark:border-slate-700">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs tabular-nums text-stone-500 dark:text-slate-400" data-range-label></span>
                    <button type="button" class="text-xs text-stone-500 dark:text-slate-400 underline hover:text-stone-800 dark:hover:text-slate-200 transition-none cursor-pointer" data-reset>Reset</button>
                </div>
                <div class="relative h-[46px] select-none touch-none text-stone-500 dark:text-slate-400" data-brush></div>
            </div>
        </div>
    @endif
</div>
@endif
