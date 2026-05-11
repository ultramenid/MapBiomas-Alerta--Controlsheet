<div class="glass rounded-sm p-5 mb-5 z-20 relative dark:text-slate-400">
    <div class="flex items-center justify-between">
        <h1 class="text-label text-stone-900 dark:text-slate-100">Users</h1>

    </div>
    @include('partials.deleterModal')
    <div class="flex sm:flex-row flex-col sm:space-y-0 space-y-4 justify-between py-4 mt-4 items-center">
        {{-- <div class="px-2 bg-black py-2 text-white cursor-pointer" wire:loading.remove wire:click="exportExcel">Export Excel</div> --}}
        <button wire:loading wire:target='exportExcel' type="button" class="bg-stone-900 dark:bg-slate-200 text-white dark:text-stone-900 py-2 px-4 text-sm font-semibold rounded-sm cursor-not-allowed transition-none">
            <svg class="animate-spin mx-auto h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </button>
        <input placeholder="type name..." class="bg-white dark:bg-slate-800 border border-stone-300 dark:border-slate-600 text-stone-900 dark:text-slate-100 w-52 rounded-sm px-3 py-2 text-sm h-9 focus:outline-none transition-none" wire:model.live='search'>
    </div>
    <div class="">
        <table class="w-full border border-stone-200 dark:border-slate-700">
            <thead class="">
                <tr class="border-b border-stone-200 dark:border-slate-700">
                    <th wire:click='sortingField("name")'  class="text-left px-3 py-2 text-label text-stone-500 dark:text-slate-400">
                        <div class="flex space-x-1  cursor-pointer" >
                            <a >Name</a>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 my-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                                </svg>
                         </div>
                     </th>
                    <th  class="text-left px-3 py-2 text-label text-stone-500 dark:text-slate-400 cursor-pointer">
                       <div class="flex space-x-1">
                           <a>Email</a>

                        </div>
                    </th>
                    <th  class="text-left px-3 py-2 text-label text-stone-500 dark:text-slate-400">
                        <div class="flex space-x-1">
                            <a>contact</a>
                         </div>
                     </th>
                     <th  class="text-left px-3 py-2 text-label text-stone-500 dark:text-slate-400">
                        <div class=" space-x-1 " >
                            <a >Level</a>

                         </div>
                     </th>

                     <th  class="text-left px-3 py-2 text-label text-stone-500 dark:text-slate-400">

                     </th>
                </tr>
            </thead>
            <tbody class="text-sm divide-y divide-stone-200 dark:divide-slate-700">
                @forelse ($databases as $item)
                <tr>
                    <td class="px-3 py-2 text-stone-700 dark:text-slate-300">
                        <a href="{{ url('/edituser/'.$item->id) }}">{{$item->name}}</a>
                    </td>
                    <td class="px-3 py-2 text-stone-700 dark:text-slate-300">
                        <a >{{ $item->email }}</a>
                    </td>
                    <td class="px-3 py-2 text-stone-700 dark:text-slate-300">
                        <a >{{$item->contact}}</a>
                    </td>
                    <td class="px-3 py-2 text-stone-700 dark:text-slate-300">
                        @switch($item->role_id)
                            @case(0)
                                <span class="inline-flex items-center justify-center text-center w-[10rem] appearance-none rounded-sm text-xs font-semibold uppercase tracking-wider bg-stone-100 dark:bg-stone-800 text-stone-700 dark:text-stone-300 border border-stone-300 dark:border-stone-600 px-3 py-1.5">Admin</span>
                                @break
                            @case(1)
                                <span class="inline-flex items-center justify-center text-center w-[10rem] appearance-none rounded-sm text-xs font-semibold uppercase tracking-wider bg-stone-100 dark:bg-stone-800 text-stone-700 dark:text-stone-300 border border-stone-300 dark:border-stone-600 px-3 py-1.5">Auditor</span>
                                @break
                            @default
                                <span class="inline-flex items-center justify-center text-center w-[10rem] appearance-none rounded-sm text-xs font-semibold uppercase tracking-wider bg-stone-100 dark:bg-stone-800 text-stone-700 dark:text-stone-300 border border-stone-300 dark:border-stone-600 px-3 py-1.5">Validator</span>
                        @endswitch
                    </td>
                    @if (session('role_id') == 0)
                    <td colspan="2" class="px-3 py-2 text-stone-500 dark:text-slate-400 relative">
                        <div class="relative flex justify-end" x-data="{ open: false }">

                            <button class=" focus:outline-none cursor-pointer" @click="open = true">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 " fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                </svg>
                            </button>

                            <ul
                                class="absolute mt-6 right-0 bg-white dark:bg-slate-800 rounded-sm shadow-lg block w-24 z-20 border border-stone-200 dark:border-slate-700"
                                x-show.transition="open"
                                @click.away="open = false"
                                x-cloak style="display: none !important">
                                <a data-turbolinks="false" href="{{ url('/edituser/'.$item->id) }}"><li class="block hover:bg-stone-100 dark:hover:bg-slate-700 cursor-pointer py-1 mt-2 px-4 text-stone-700 dark:text-slate-300 transition-none" @click.away="open = false">Edit</li></a>
                                <li class="block hover:bg-stone-100 dark:hover:bg-slate-700 cursor-pointer py-1 mb-2 px-4 text-stone-700 dark:text-slate-300 transition-none"  wire:click="delete({{ $item->id }})" @click.away="open = false">Delete</li>
                            </ul>
                        </div>
                    </td>
                    @endif
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-3 py-2 text-stone-500 dark:text-slate-400">
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
