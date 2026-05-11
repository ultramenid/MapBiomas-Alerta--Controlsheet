<div class="glass rounded-sm p-5 mb-5 z-20 relative dark:text-slate-400">
    <h1 class="text-label text-stone-900 dark:text-slate-100">Alerts</h1>
    <div class="flex gap-4 sm:flex-row flex-col mt-4 items-end justify-between">
        <div class="flex gap-4 items-end">
        <div class="flex flex-col">
            <span class="text-label text-stone-600 dark:text-slate-400">Search</span>
            <input class="bg-white dark:bg-slate-800 border border-stone-300 dark:border-slate-600 text-stone-900 dark:text-slate-100 w-52 rounded-sm px-3 py-2 text-sm h-9 focus:outline-none transition-none" wire:model.live='searchId' placeholder="alert ID">
        </div>
        <div class="flex flex-col">
            <span class="text-label text-stone-600 dark:text-slate-400">Status</span>
            <div class="sm:w-40 w-full relative flex  flex-col  text-neutral-600 dark:text-neutral-300">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="absolute pointer-events-none right-4 top-2 size-5">
                    <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                </svg>
                <select wire:ignore wire:model.live='selectStatus'class="w-full appearance-none bg-white dark:bg-slate-800 border border-stone-300 dark:border-slate-600 text-stone-900 dark:text-slate-100 px-3 py-2 text-sm h-9 rounded-sm focus:outline-none cursor-pointer transition-none">
                    <option value="all">All</option>
                    <option value="pre-approved">Pre-approved</option>
                    <option value="refined">Refined</option>
                    <option value="error">Error</option>
                    <option value="reexportimage">Re-export image</option>
                    <option value="reclassification">Re-classification</option>
                    <option value="pending">Pending</option>
                </select>
            </div>
        </div>
        <div class="flex flex-col">
            <span class="text-label text-stone-600 dark:text-slate-400">Tahun</span>
            <div class="sm:w-36 w-full relative flex  flex-col  text-neutral-600 dark:text-neutral-300">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="absolute pointer-events-none right-4 top-2 size-5">
                    <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                </svg>
                <select wire:ignore id='date-dropdown' wire:model.live="yearAlert" class="w-full appearance-none bg-white dark:bg-slate-800 border border-stone-300 dark:border-slate-600 text-stone-900 dark:text-slate-100 px-3 py-2 text-sm h-9 rounded-sm focus:outline-none cursor-pointer transition-none">
                    <option value="all">all</option>
                </select>
            </div>

        </div>
        </div>





    </div>
    @include('partials.auditing')
    @include('partials.deleterAlert')


    <div class="mt-4">
        <div wire:loading class="flex w-full justify-center text-center bg-red-400 dark:bg-red-900 py-2 animate-pulse text-xs px-4 text-white rounded-sm" >loading. . .</div>
        <table class="w-full border border-stone-200 dark:border-slate-700">
            <thead class=" text-xs">
                <tr class="border-b border-stone-200 dark:border-slate-700">
                    <th   class="text-left px-3 py-2 text-label text-stone-500 dark:text-slate-400 cursor-pointer">
                        <div class=" space-x-1 flex" >
                            <span>Alert ID</span>

                         </div>
                     </th>
                    <th  class="text-left px-3 py-2 text-label text-stone-500 dark:text-slate-400 cursor-pointer hidden sm:table-cell">
                       <div class="flex space-x-1">
                           <span>Input date</span>

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
                     <th  class="text-center px-3 py-2 text-label text-stone-500 dark:text-slate-400 cursor-pointer">
                        <div class="flex space-x-1">
                            <span>Platform Status</span>

                         </div>
                     </th>



                     <th class="text-right px-3 py-2 text-label text-stone-500 dark:text-slate-400">

                    </th>


                </tr>
            </thead>
            <tbody class="bg-white dark:bg-slate-900  divide-y divide-stone-200 dark:divide-slate-700">
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
                    <td class="px-3 py-2 break-words text-xs  text-stone-700 dark:text-slate-300" wire:key="alert-{{ $item->alertId }}">
                        @if (in_array($item->auditorStatus, ['pre-approved', 'refined', 'error']))
                            <button
                                wire:loading.attr='disabled'
                                onclick="window.dispatchEvent(
                                new CustomEvent('open-audit-modal',
                                { detail: { id: {{ $item->id }} } })
                                )" @click.away="open = false" class="inline-flex items-center justify-center text-center w-[10rem] appearance-none rounded-sm text-xs font-semibold uppercase tracking-wider
                                @if($item->auditorStatus == 'pre-approved') bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-700 cursor-pointer
                                @elseif($item->auditorStatus == 'refined') bg-[#87bed3]/20 dark:bg-[#87bed3]/30 text-[rgb(70,130,150)] dark:text-[rgb(180,220,235)] border border-[#87bed3]/40 dark:border-[#87bed3]/50 cursor-pointer
                                @elseif($item->auditorStatus == 'error') bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-700 cursor-pointer
                                @endif
                                px-3 py-1.5">{{ $item->auditorStatus }}
                    </button>
                        @elseif ($item->auditorStatus == 'approved')
                            <span class="inline-flex items-center justify-center text-center w-[10rem] appearance-none rounded-sm text-xs font-semibold uppercase tracking-wider bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300 border border-green-200 dark:border-green-700 px-3 py-1.5">{{$item->auditorStatus}}</span>
                        @elseif ($item->auditorStatus == 'pending')
                            <span class="inline-flex items-center justify-center text-center w-[10rem] appearance-none rounded-sm text-xs font-semibold uppercase tracking-wider bg-stone-100 dark:bg-stone-800 text-stone-700 dark:text-stone-300 border border-stone-300 dark:border-stone-600 px-3 py-1.5">{{$item->auditorStatus}}</span>
                        @elseif ($item->auditorStatus == 'duplicate' or $item->auditorStatus == 'rejected')
                            <span class="inline-flex items-center justify-center text-center w-[10rem] appearance-none rounded-sm text-xs font-semibold uppercase tracking-wider bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-700 px-3 py-1.5">{{$item->auditorStatus}}</span>
                        @else
                            <span class="inline-flex items-center justify-center text-center w-[10rem] appearance-none rounded-sm text-xs font-semibold uppercase tracking-wider bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-700 px-3 py-1.5">{{$item->auditorStatus}}</span>
                        @endif
                    </td>

                    <td class="text-center px-3 py-2">
                        <svg  wire:click="deleteAlert({{ $item->alertId }})" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-red-600 dark:text-red-400 cursor-pointer transition-none">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                        </svg>
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

    <div class="mt-4">
    @if ($databases)
        {{ $databases->links('livewire.pagination') }}
        @endif
    </div>



    <script>
        let dateDropdown = document.getElementById('date-dropdown');

        let currentYear = new Date().getFullYear();
        let earliestYear = 2020;
        while (currentYear >= earliestYear) {
            let dateOption = document.createElement('option');
            dateOption.text = currentYear;
            dateOption.value = currentYear;
            dateDropdown.add(dateOption);
            currentYear -= 1;
        }

    </script>
</div>