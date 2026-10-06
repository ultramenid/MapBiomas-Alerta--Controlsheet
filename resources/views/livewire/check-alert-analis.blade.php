<div class="glass rounded-sm p-5 mb-5 z-20 relative dark:text-slate-400">
    <div class="text-sm mb-6">
        <a class="text-label text-stone-600 dark:text-slate-400 mb-1">Alert status by validator</a>
        <div class="mt-2 flex gap-2">
            <input class="bg-white dark:bg-slate-800 border border-stone-300 dark:border-slate-600 text-stone-900 dark:text-slate-100 w-52 rounded-sm px-3 py-2 text-sm h-9 focus:outline-none transition-none" wire:model.live.debounce.300ms='searchName' placeholder="validator name">
        </div>
    </div>

    <div wire:loading.delay class="w-full bg-stone-900 dark:bg-slate-200 h-0.5 animate-pulse rounded-sm mb-2"></div>

    <div class="overflow-y-auto w-full">
        <table class="w-full border-collapse">
          <thead class="text-xs font-semibold">
            <tr class="text-left">
              <th wire:click='sortingField("name")' class="text-left px-3 py-2.5 text-label text-stone-500 dark:text-slate-400 cursor-pointer capitalize border-b border-stone-200 dark:border-slate-700">Validator</th>
              <th wire:click='sortingField("approved")' class="cursor-pointer border-b border-stone-300 dark:border-slate-700 px-2 py-2 capitalize ">Approved</th>
              <th wire:click='sortingField("reexportimage")' class="cursor-pointer border-b border-stone-300 dark:border-slate-700 px-2 py-2 capitalize">reexportimage</th>
              <th wire:click='sortingField("reclassification")' class="cursor-pointer border-b border-stone-300 dark:border-slate-700 px-2 py-2 capitalize">reclassification</th>
              <th wire:click='sortingField("rejected")' class="cursor-pointer border-b border-stone-300 dark:border-slate-700 px-2 py-2 capitalize">Rejected</th>
              <th wire:click='sortingField("duplicate")' class="cursor-pointer border-b border-stone-300 dark:border-slate-700 px-2 py-2 capitalize">Duplicate</th>
              <th wire:click='sortingField("preapproved")' class="cursor-pointer border-b border-stone-300 dark:border-slate-700 px-2 py-2 capitalize">pre-approved</th>
              <th wire:click='sortingField("refined")' class="cursor-pointer border-b border-stone-300 dark:border-slate-700 px-2 py-2 capitalize">refined</th>
              <th wire:click='sortingField("error")' class="cursor-pointer border-b border-stone-300 dark:border-slate-700 px-2 py-2 capitalize">error</th>
              <th wire:click='sortingField("total")' class="cursor-pointer border-b border-stone-300 dark:border-slate-700 px-2 py-2 capitalize">TOTAL</th>
              <th wire:click='sortingField("percent")' class="cursor-pointer border-b border-stone-300 dark:border-slate-700 px-3 py-2 text-right whitespace-nowrap" title="Approved / (Total − Rejected) × 100">Approval %</th>
            </tr>
          </thead>
          <tbody class="text-label text-stone-500 dark:text-slate-400">
            @forelse ($alerts as $item )
                <tr class="border-t border-stone-200 dark:border-slate-700 hover:bg-stone-50 dark:hover:bg-slate-800 transition-none {{ $item->is_monitored ? 'bg-green-50/70 dark:bg-green-900/15' : '' }}">
                    <td class="px-3 py-2.5 text-stone-700 dark:text-slate-300 border-b border-stone-200 dark:border-slate-700 {{ $item->is_monitored ? 'border-l-2 border-l-green-700 dark:border-l-green-400' : '' }}">
                        <a href="{{ url('/alertanalis/'.$item->userId) }}" class="hover:underline inline-flex items-center gap-1.5">
                            @if ($item->is_monitored)
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-3.5 text-green-700 dark:text-green-400" aria-label="Monitored"><title>Monitored</title><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                            @endif
                            {{$item->name}}
                        </a>
                    </td>
                    <td class="px-3 py-2.5 text-stone-700 dark:text-slate-300 border-b border-stone-200 dark:border-slate-700 st-cell st-approved">{{$item->approved}}</td>
                    <td class="px-3 py-2.5 text-stone-700 dark:text-slate-300 border-b border-stone-200 dark:border-slate-700 st-cell st-reexportimage">{{$item->reexportimage}}</td>
                    <td class="px-3 py-2.5 text-stone-700 dark:text-slate-300 border-b border-stone-200 dark:border-slate-700 st-cell st-reclassification">{{$item->reclassification}}</td>
                    <td class="px-3 py-2.5 text-stone-700 dark:text-slate-300 border-b border-stone-200 dark:border-slate-700 st-cell st-rejected">{{$item->rejected}}</td>
                    <td class="px-3 py-2.5 text-stone-700 dark:text-slate-300 border-b border-stone-200 dark:border-slate-700 st-cell st-duplicate">{{$item->duplicate}}</td>
                    <td class="px-3 py-2.5 text-stone-700 dark:text-slate-300 border-b border-stone-200 dark:border-slate-700 st-cell st-pre-approved">{{$item->preapproved}}</td>
                    <td class="px-3 py-2.5 text-stone-700 dark:text-slate-300 border-b border-stone-200 dark:border-slate-700 st-cell st-refined">{{$item->refined}}</td>
                    <td class="px-3 py-2.5 text-stone-700 dark:text-slate-300 border-b border-stone-200 dark:border-slate-700 st-cell st-error">{{$item->error}}</td>
                    <td class="px-3 py-2.5 text-stone-700 dark:text-slate-300 border-b border-stone-200 dark:border-slate-700">{{$item->total}}</td>
                    <td class="px-3 py-2.5 border-b border-stone-200 dark:border-slate-700" title="{{ $item->approved }} / ({{ $item->total }} − {{ $item->rejected }})">
                        @if ($item->percent === null)
                            <div class="text-right text-stone-400 dark:text-slate-500">—</div>
                        @else
                            {{-- value over a thin approved-coloured bar, scaled 0–100% --}}
                            <div class="w-24 ml-auto">
                                <div class="text-right font-semibold tabular-nums text-stone-900 dark:text-slate-200">{{ number_format($item->percent, 1) }}%</div>
                                <div class="mt-1 h-1 w-full rounded-sm bg-stone-200 dark:bg-slate-700 overflow-hidden">
                                    <div class="h-full rounded-sm" style="width: {{ min(100, max(0, $item->percent)) }}%; background: var(--st-approved)"></div>
                                </div>
                            </div>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td class="px-3 py-2.5 text-stone-700 dark:text-slate-300 border-b border-stone-200 dark:border-slate-700">No data found</td>
                </tr>
            @endforelse


          </tbody>
        </table>
      </div>

      {{-- @if ($alerts)
      {{ $alerts->links('livewire.pagination') }}
      @endif --}}

</div>
