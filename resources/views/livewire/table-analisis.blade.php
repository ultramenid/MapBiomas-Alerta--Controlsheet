<div class="glass rounded-sm p-5 mb-5 z-20 relative dark:text-slate-400">
    <a class="text-label text-stone-600 dark:text-slate-400 mb-1">Alert Need to Fix</a>
    <div x-data="{ open: @entangle('isReason') }">
        @include('partials.auditorReason')
    </div>
    <input class="bg-white dark:bg-slate-900 border border-stone-300 dark:border-slate-600 text-stone-900 dark:text-slate-100 w-52 rounded-sm px-3 py-2 text-sm focus:outline-none transition-none mt-2" wire:model.live='search' placeholder="alert ID">
    <div class="mt-4">
        <table class="w-full border-collapse">
            <thead class=" text-xs">
                <tr class="">
                    <th wire:click='sortingField("alertId")'  class="text-left px-3 py-2.5 text-label text-stone-500 dark:text-slate-400 cursor-pointer">
                        <div class=" space-x-1 flex" >
                            <a class="text-label text-stone-500 dark:text-slate-400">Alert ID</a>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 my-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                                </svg>
                         </div>
                     </th>
                    <th wire:click='sortingField("created_at")' class="text-left px-3 py-2.5 text-label text-stone-500 dark:text-slate-400 cursor-pointer hidden sm:table-cell">
                       <div class="flex space-x-1">
                           <a>Input date</a>
                           <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 my-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                            </svg>
                        </div>
                    </th>
                    <th  class="text-left px-3 py-2.5 text-label text-stone-500 dark:text-slate-400 hidden sm:table-cell">
                        <div class="flex space-x-1">
                            <a>Region/Island</a>
                         </div>
                     </th>
                     <th  class="text-left px-3 py-2.5 text-label text-stone-500 dark:text-slate-400 hidden sm:table-cell">
                        <div class=" space-x-1 " >
                            <a >Province</a>

                         </div>
                     </th>
                     <th wire:click='sortingField("auditorStatus")' class="text-left px-3 py-2.5 text-label text-stone-500 dark:text-slate-400 cursor-pointer">
                        <div class="flex space-x-1">
                            <a>Auditor Status</a>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 my-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                             </svg>
                         </div>
                     </th>






                    <th class="text-right px-3 py-2.5 text-label text-stone-500 dark:text-slate-400 hidden sm:table-cell">

                    </th>
                </tr>
            </thead>
            <tbody class="text-sm">
                @forelse ($databases as $item)
                <tr>
                    <td class="px-3 py-2.5 text-stone-700 dark:text-slate-300">
                        <a>{{$item->alertId}}</a>
                    </td>
                    <td class="px-3 py-2.5 text-stone-700 dark:text-slate-300 hidden sm:table-cell">
                        @php
                            $date = \Carbon\Carbon::parse($item->created_at)->locale(App::getLocale());
                            $date->settings(['formatFunction' => 'translatedFormat']);
                        @endphp</h1>
                        <a>{{ $date->format('d-m-Y')  }}</a>
                    </td>

                    <td class="px-3 py-2.5 text-stone-700 dark:text-slate-300 hidden sm:table-cell">
                        <a >{{$item->region}}</a>
                    </td>
                    <td class="px-3 py-2.5 text-stone-700 dark:text-slate-300 hidden sm:table-cell">
                        <a >{{$item->province}}</a>
                    </td>
                    <td class="px-6 py-4 break-words text-xs  text-stone-700 dark:text-slate-300">
                        @if (!$item->auditorStatus)
                            <a class="rounded-sm px-2 py-1 bg-stone-300 text-stone-700 dark:bg-slate-700 dark:text-slate-300">Pending</a>
                        @elseif ($item->auditorStatus == 'approved')
                            <a  class="rounded-sm px-2 py-1 bg-green-alerta text-white">{{$item->auditorStatus}}</a>
                        @elseif ($item->auditorStatus == 'duplicate' or $item->auditorStatus == 'rejected')
                            <a  class="rounded-sm px-2 py-1 bg-merah-alerta text-white">{{$item->auditorStatus}}</a>
                        @else
                            <a  onclick="window.dispatchEvent(
                                new CustomEvent('open-reason-modal', {
                                    detail: { id: {{ $item->alertId}} }
                                })
                            )" @click.away="open = false" class="rounded-sm px-2 py-1 bg-yellow-alerta text-white cursor-pointer transition-none">{{$item->auditorStatus}}</a>
                        @endif
                    </td>

                    <td class="px-3 py-2.5 text-stone-700 dark:text-slate-300">
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
                    <td colspan="5" class="px-3 py-2.5 text-stone-700 dark:text-slate-300">
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
