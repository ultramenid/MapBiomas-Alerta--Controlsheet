<div class="glass rounded-sm p-5 mb-5 z-20 relative dark:text-slate-400">
    <h1 class="text-label text-stone-900 dark:text-slate-100 mb-1">Alert Need to Fix</h1>
    @include('partials.auditorReason')
    <div class="flex gap-4 mt-4 items-end">
        <div class="flex flex-col">
            <span class="text-label text-stone-600 dark:text-slate-400">Search</span>
            <input class="bg-white dark:bg-slate-800 border border-stone-300 dark:border-slate-600 text-stone-900 dark:text-slate-100 w-52 rounded-sm px-3 py-2 text-sm h-9 focus:outline-none transition-none" wire:model.live='search' placeholder="alert ID">
        </div>
    </div>
    <div class="mt-4">
        <table class="w-full border border-stone-200 dark:border-slate-700">
            <thead class=" text-xs">
                <tr class="border-b border-stone-200 dark:border-slate-700">
                    <th wire:click='sortingField("alertId")'  class="text-left px-3 py-2 text-label text-stone-500 dark:text-slate-400 cursor-pointer">
                        <div class=" space-x-1 flex" >
                            <span>Alert ID</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 my-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                                </svg>
                         </div>
                     </th>
                    <th wire:click='sortingField("created_at")' class="text-left px-3 py-2 text-label text-stone-500 dark:text-slate-400 cursor-pointer hidden sm:table-cell">
                       <div class="flex space-x-1">
                           <span>Input date</span>
                           <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 my-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                            </svg>
                        </div>
                    </th>
                    <th  class="text-left px-3 py-2 text-label text-stone-500 dark:text-slate-400 hidden sm:table-cell">
                        <div class="flex space-x-1">
                            <span>Region/Island</span>
                         </div>
                     </th>
                     <th  class="text-left px-3 py-2 text-label text-stone-500 dark:text-slate-400 hidden sm:table-cell">
                        <div class=" space-x-1 " >
                            <span>Province</span>

                         </div>
                     </th>
                     <th wire:click='sortingField("auditorStatus")' class="text-left px-3 py-2 text-label text-stone-500 dark:text-slate-400 cursor-pointer">
                        <div class="flex space-x-1">
                            <span>Auditor Status</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 my-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                             </svg>
                         </div>
                     </th>






                    <th class="text-right px-3 py-2 text-label text-stone-500 dark:text-slate-400 hidden sm:table-cell">

                    </th>
                </tr>
            </thead>
            <tbody class="text-sm bg-white dark:bg-slate-900 divide-y divide-stone-200 dark:divide-slate-700">
                @forelse ($databases as $item)
                <tr>
                    <td class="px-3 py-2 text-stone-700 dark:text-slate-300">
                        {{$item->alertId}}
                    </td>
                    <td class="px-3 py-2 text-stone-700 dark:text-slate-300 hidden sm:table-cell">
                        @php
                            $date = \Carbon\Carbon::parse($item->created_at)->locale(App::getLocale());
                            $date->settings(['formatFunction' => 'translatedFormat']);
                        @endphp
                        {{ $date->format('d-m-Y')  }}
                    </td>

                    <td class="px-3 py-2 text-stone-700 dark:text-slate-300 hidden sm:table-cell">
                        {{$item->region}}
                    </td>
                    <td class="px-3 py-2 text-stone-700 dark:text-slate-300 hidden sm:table-cell">
                        {{$item->province}}
                    </td>
                    <td class="px-3 py-2 break-words text-xs  text-stone-700 dark:text-slate-300">
                        @if (!$item->auditorStatus)
                            <span class="inline-flex items-center justify-center text-center w-[10rem] appearance-none rounded-sm text-xs font-semibold uppercase tracking-wider bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-700 px-3 py-1.5">Pending</span>
                        @elseif ($item->auditorStatus == 'approved')
                            <span class="inline-flex items-center justify-center text-center w-[10rem] appearance-none rounded-sm text-xs font-semibold uppercase tracking-wider bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300 border border-green-200 dark:border-green-700 px-3 py-1.5">{{$item->auditorStatus}}</span>
                        @elseif ($item->auditorStatus == 'duplicate' or $item->auditorStatus == 'rejected')
                            <span class="inline-flex items-center justify-center text-center w-[10rem] appearance-none rounded-sm text-xs font-semibold uppercase tracking-wider bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-700 px-3 py-1.5">{{$item->auditorStatus}}</span>
                        @else
                            <span onclick="window.dispatchEvent(
                                new CustomEvent('open-reason-modal', {
                                    detail: { id: {{ $item->alertId}} }
                                })
                            )" @click.away="open = false" class="inline-flex items-center justify-center text-center w-[10rem] appearance-none rounded-sm text-xs font-semibold uppercase tracking-wider bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-700 px-3 py-1.5 cursor-pointer transition-none">{{$item->auditorStatus}}</span>
                        @endif
                    </td>

                    <td class="px-3 py-2 text-stone-700 dark:text-slate-300">
                        <div class="relative flex justify-end" x-data="{ open: false }">

                            <a href="{{ url('/editalert/'.$item->alertId) }}" class=" focus:outline-none cursor-pointer" @click="open = true">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                  </svg>
                            </a>


                        </div>
                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-3 py-2 text-stone-700 dark:text-slate-300">
                        No data found
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($databases)
    {{ $databases->links('livewire.pagination') }}
    @endif
</div>
