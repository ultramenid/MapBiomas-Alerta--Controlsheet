<div class="glass rounded-sm p-5 mb-5 z-20 relative dark:text-slate-400">
    <div class="text-sm mb-6">
        <a class="text-label text-stone-600 dark:text-slate-400 mb-1">Alert status by validator</a>
        <div class="mt-2 flex gap-2">
            <input class="bg-white dark:bg-slate-800 border border-stone-300 dark:border-slate-600 text-stone-900 dark:text-slate-100 w-52 rounded-sm px-3 py-2 text-sm h-9 focus:outline-none transition-none" wire:model.live='searchName' placeholder="validator name">
        </div>
    </div>

    <div class="overflow-y-auto w-full">
        <table class="w-full border-collapse">
          <thead class="text-xs font-semibold">
            <tr class="text-left">
              <th wire:click='sortingField("name")' class="text-left px-3 py-2.5 text-label text-stone-500 dark:text-slate-400 cursor-pointer capitalize border-b border-stone-200 dark:border-slate-700">Validator</th>
              <th wire:click='sortingField("approved")' class="cursor-pointer border-b border-stone-300 dark:border-slate-700 px-2 py-2 capitalize ">Aprroved</th>
              <th wire:click='sortingField("reexportimage")' class="cursor-pointer border-b border-stone-300 dark:border-slate-700 px-2 py-2 capitalize">reexportimage</th>
              <th wire:click='sortingField("reclassification")' class="cursor-pointer border-b border-stone-300 dark:border-slate-700 px-2 py-2 capitalize">reclassification</th>
              <th wire:click='sortingField("rejected")' class="cursor-pointer border-b border-stone-300 dark:border-slate-700 px-2 py-2 capitalize">Rejected</th>
              <th wire:click='sortingField("duplicate")' class="cursor-pointer border-b border-stone-300 dark:border-slate-700 px-2 py-2 capitalize">Duplicate</th>
              <th wire:click='sortingField("preapproved")' class="cursor-pointer border-b border-stone-300 dark:border-slate-700 px-2 py-2 capitalize">pre-approved</th>
              <th wire:click='sortingField("refined")' class="cursor-pointer border-b border-stone-300 dark:border-slate-700 px-2 py-2 capitalize">refined</th>
              <th class="cursor-pointer border-b border-stone-300 dark:border-slate-700 px-2 py-2 capitalize">Sccon</th>
              <th class="cursor-pointer border-b border-stone-300 dark:border-slate-700 px-2 py-2 capitalize">Workspace</th>
              <th wire:click='sortingField("total")' class="cursor-pointer border-b border-stone-300 dark:border-slate-700 px-2 py-2 capitalize">TOTAL</th>
            </tr>
          </thead>
          <tbody class="text-label text-stone-500 dark:text-slate-400">
            @forelse ($alerts as $item )
                <tr class="border-t border-stone-200 dark:border-slate-700 hover:bg-stone-50 dark:hover:bg-slate-800 transition-none">
                    <td class="px-3 py-2.5 text-stone-700 dark:text-slate-300 border-b border-stone-200 dark:border-slate-700"><a href="{{ url('/alertanalis/'.$item->userId) }}" class="hover:underline">{{$item->name}}</a></td>
                    <td class="px-3 py-2.5 text-stone-700 dark:text-slate-300 border-b border-stone-200 dark:border-slate-700 bg-green-alerta-table-full">{{$item->approved}}</td>
                    <td class="px-3 py-2.5 text-stone-700 dark:text-slate-300 border-b border-stone-200 dark:border-slate-700 bg-yellow-alerta-table-full">{{$item->reexportimage}}</td>
                    <td class="px-3 py-2.5 text-stone-700 dark:text-slate-300 border-b border-stone-200 dark:border-slate-700 bg-yellow-alerta-table-full">{{$item->reclassification}}</td>
                    <td class="px-3 py-2.5 text-stone-700 dark:text-slate-300 border-b border-stone-200 dark:border-slate-700 bg-merah-alerta-table-full">{{$item->rejected}}</td>
                    <td class="px-3 py-2.5 text-stone-700 dark:text-slate-300 border-b border-stone-200 dark:border-slate-700 bg-merah-alerta-table-full">{{$item->duplicate}}</td>
                    <td class="px-3 py-2.5 text-stone-700 dark:text-slate-300 border-b border-stone-200 dark:border-slate-700 bg-refined-alerta-table-full">{{$item->preapproved}}</td>
                    <td class="px-3 py-2.5 text-stone-700 dark:text-slate-300 border-b border-stone-200 dark:border-slate-700 bg-refined-alerta-table-full">{{$item->refined}}</td>
                    <td class="px-3 py-2.5 text-stone-700 dark:text-slate-300 border-b border-stone-200 dark:border-slate-700 bg-gray-alerta-table-full">{{$item->sccon}}</td>
                    <td class="px-3 py-2.5 text-stone-700 dark:text-slate-300 border-b border-stone-200 dark:border-slate-700 bg-gray-alerta-table-full">{{$item->workspace}}</td>
                    <td class="px-3 py-2.5 text-stone-700 dark:text-slate-300 border-b border-stone-200 dark:border-slate-700 bg-gray-alerta-table-full">{{$item->total}}</td>
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
