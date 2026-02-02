<div class="mt-2  py-6 px-4 border border-gray-100 z-20 relative  bg-white">
    <a class="">Alerts</a>
    <div x-data="{ open: @entangle('isReason') }">
        @include('partials.auditorReason')
    </div>
    <div class="flex gap-3 items-end">
        <input class="sm:w-52 w-full py-2 border-gray-500 border px-2 focus:outline-none mt-2 text-xs" wire:model.live='search' placeholder="alert ID">
        <div class="flex flex-col">
            <a class="text-xs ">Tahun</a>
            <div class="sm:w-36 w-full relative flex  flex-col  text-neutral-600 dark:text-neutral-300">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="absolute pointer-events-none right-4 top-2 size-5">
                    <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                </svg>
                <select wire:ignore id='date-dropdown' wire:model.live="yearAlert" class=" w-full appearance-none text-black  border border-neutral-300 bg-gray-100 px-4 py-2 text-xs focus:outline-none">
                    <option value="all">All</option>
                </select>
            </div>

        </div>
        <div class="flex flex-col">
            <a class="text-xs ">Status</a>
            <div class="sm:w-40 w-full relative flex  flex-col  text-neutral-600 dark:text-neutral-300">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="absolute pointer-events-none right-4 top-2 size-5">
                    <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                </svg>
                <select wire:ignore wire:model.live='selectStatus'class=" w-full appearance-none text-black  border border-neutral-300 bg-gray-100 px-4 py-2 text-xs focus:outline-none">
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
    </div>
    <div class="mt-4">
        <div wire:loading class="flex w-full justify-center text-center bg-red-300 py-1 animate-pulse text-xs px-4 text-white" >loading. . .</div>
        <table class="w-full divide-y divide-gray-200  rounded-sm  border border-gray-100">
            <thead class=" text-xs">
                <tr class="">
                    <th  class="bg-gray-50 px-6 py-1  cursor-pointer   text-left  text-gray-700 uppercase tracking-wider  sm:w-2/12 w-4/12">
                        <div class=" space-x-1 flex" >
                            <a class="text-xs">Alert ID</a>
                         </div>
                     </th>
                    <th  class="bg-gray-50 px-6 py-1    text-left   text-gray-700 uppercase tracking-wider cursor-pointer sm:w-2/12 w-4/12 hidden sm:table-cell ">
                       <div class="flex space-x-1">
                           <a>Input date</a>

                        </div>
                    </th>
                    <th  class="bg-gray-50 px-6 py-1    text-left   text-gray-700 uppercase tracking-wider  sm:w-3/12 w-4/12 hidden sm:table-cell">
                        <div class="flex space-x-1">
                            <a>Region/Island</a>
                         </div>
                     </th>
                     <th  class="bg-gray-50 px-6 py-1   text-left   text-gray-700 uppercase tracking-wider  sm:w-3/12 w-11/12 hidden sm:table-cell">
                        <div class=" space-x-1 " >
                            <a >Province</a>

                         </div>
                     </th>
                     <th  class="bg-gray-50 px-2 py-1   text-center   text-gray-700 uppercase tracking-wider cursor-pointer sm:w-2/12 w-4/12">
                        <div class="flex space-x-1">
                            <a>Platform Status</a>

                         </div>
                     </th>



                     <th class=" text-right bg-gray-50   text-gray-700 uppercase tracking-wider sm:w-1/12 table-cell">

                    </th>


                </tr>
            </thead>
            <tbody class="bg-white  divide-y divide-gray-200 ">
                @forelse ($databases as $item)
                <tr>
                    <td class="px-6 py-1 break-words text-xs  text-gray-700 ">
                        <a>{{$item->alertId}}</a>
                    </td>
                    <td class="px-6 py-1 break-words text-xs  text-gray-700 hidden sm:table-cell">
                        @php
                            $date = \Carbon\Carbon::parse($item->created_at)->locale(App::getLocale());
                            $date->settings(['formatFunction' => 'translatedFormat']);
                        @endphp</h1>
                        <a>{{ $date->format('d-m-Y')  }}</a>
                    </td>

                    <td class="px-6 py-1 break-words text-xs  text-gray-700 hidden sm:table-cell">
                        <a >{{$item->region}}</a>
                    </td>
                    <td class="px-6 py-1 break-words text-xs  text-gray-700 hidden sm:table-cell">
                        <a >{{$item->province}}</a>
                    </td>
                    <td class="px-2 py-1 break-words text-xs text-gray-700">
                        <div class="relative inline-flex items-center">
                            @if(in_array($item->auditorStatus, ['approved', 'rejected', 'duplicate']))

                                @if ($item->auditorStatus == 'approved')
                                    <div class="w-36 flex items-center justify-center border px-2 py-1 text-xs rounded focus:outline-none bg-green-alerta text-white">
                                        Approved
                                    </div>
                                @elseif ($item->auditorStatus == 'rejected')
                                    <div class="w-36 flex items-center justify-center border px-2 py-1 text-xs rounded focus:outline-none bg-merah-alerta text-white">
                                        Rejected
                                    </div>
                                @elseif ($item->auditorStatus == 'duplicate')
                                    <div class="w-36 flex items-center justify-center border px-2 py-1 text-xs rounded focus:outline-none bg-merah-alerta text-white">
                                        Duplicate
                                    </div>
                                @endif




                            @elseif(in_array($item->auditorStatus, ['reexportimage', 'reclassification']))
                            <div wire:click='showReason({{ $item->id }})'
                                class="w-36 flex items-center justify-center px-2 py-1 text-xs rounded focus:outline-none bg-yellow-alerta text-white cursor-pointer">
                                {{ $item->auditorStatus == 'reexportimage'
                                    ? 'Re-export images'
                                    : ($item->auditorStatus == 'reclassification'
                                        ? 'Re-classification'
                                        : ucfirst(str_replace('-', ' ', $item->auditorStatus ?? 'Unknown')))
                                }}
                            </div>

                            @else
                            <!-- wrapper centers the select visually; the inline style helps Safari center the selected option -->
                            <div class="w-36 flex items-center justify-center relative" wire:key="alert-{{ $item->alertId }}">
                                <select
                                onchange="Livewire.dispatch('updateStatus', { id: {{ $item->alertId }}, status: this.value })"
                                class="w-full text-center appearance-none px-2 py-1 text-xs rounded focus:outline-none
                                    @if($item->auditorStatus == 'pre-approved') bg-blue-100 text-gray-700 cursor-pointer
                                    @elseif($item->auditorStatus == 'refined') bg-[#87bed3] text-white cursor-pointer
                                    @elseif($item->auditorStatus == 'error') bg-merah-alerta text-white cursor-pointer
                                    @elseif($item->auditorStatus == 'pending') bg-black text-white cursor-pointer
                                    @else bg-gray-100 text-black @endif"
                                @if(!in_array($item->auditorStatus, ['pre-approved', 'refined', 'error', 'pending'])) disabled @endif
                                style="-webkit-text-align-last: center; text-align-last: center;"
                                >
                                <option value="pre-approved" {{ $item->auditorStatus == 'pre-approved' ? 'selected' : '' }}>Pre-Approved</option>
                                <option value="refined" {{ $item->auditorStatus == 'refined' ? 'selected' : '' }}>Refined</option>
                                <option value="error" {{ $item->auditorStatus == 'error' ? 'selected' : '' }}>Error</option>
                                <option value="pending" {{ $item->auditorStatus == 'pending' ? 'selected' : '' }}>Pending</option>
                                </select>

                                {{-- SVG icon (visible when enabled) --}}
                                @if(in_array($item->auditorStatus, ['pre-approved', 'refined']))
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                    class="absolute right-2 h-4 w-4 text-gray-700 pointer-events-none">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15 12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                                </svg>
                                @elseif ($item->auditorStatus == 'error')
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                    class="absolute right-2 h-4 w-4 text-yellow-400 pointer-events-none">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15 12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                                </svg>
                                @elseif ($item->auditorStatus == 'pending')
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                    class="absolute right-2 h-4 w-4 bg-white pointer-events-none">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15 12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                                </svg>
                                @endif
                            </div>
                            @endif
                            </div>
                        </td>


                    <td class="text-xs px-4">
                        <div class="relative flex justify-end" x-data="{ open: false }">
                            @if (!in_array($item->auditorStatus, ['approved', 'rejected', 'duplicate']) )
                                <a href="{{ url('/editalert/'.$item->alertId) }}" class=" focus:outline-none cursor-pointer" @click="open = true">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                    </svg>
                                </a>
                            @endif



                        </div>
                    </td>
                    <td></td>

                </tr>
                @empty
                <tr>
                    <td colspan="5" class="whitespace-nowrap text-xs text-gray-700 px-6 py-3">
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
