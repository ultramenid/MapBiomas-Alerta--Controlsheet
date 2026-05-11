<div x-data="{ open: @entangle('deleter') }" x-effect="document.body.classList.toggle('overflow-hidden', open)" @keydown.escape.window="open = false">
    <template x-teleport="body">
        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            {{-- Backdrop --}}
            <div x-show="open" x-transition.opacity.duration.200ms class="fixed inset-0 bg-black/40" @click="open = false"></div>

            {{-- Panel --}}
            <div x-show="open" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" @click.stop class="relative w-full sm:max-w-lg bg-white dark:bg-slate-800 rounded-sm shadow-xl">
                <div class="px-5 py-5">
                    {{-- Header --}}
                    <div class="flex items-center justify-between mb-5 pb-4 border-b border-stone-200 dark:border-slate-600">
                        <h2 class="text-lg font-bold text-stone-900 dark:text-slate-100">Delete User</h2>
                        <button wire:click='closeDelete' class="text-stone-400 hover:text-stone-600 dark:text-slate-400 dark:hover:text-slate-200 cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Body --}}
                    <div class="flex items-center gap-4 mb-5">
                        <div class="w-10 h-10 shrink-0 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-red-600 dark:text-red-400">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                        </div>
                        <p class="text-sm text-stone-500 dark:text-slate-400">Are you sure you want to delete <span class="font-semibold text-stone-900 dark:text-slate-200">{{ $deleteName }}</span>? This action cannot be undone.</p>
                    </div>

                    {{-- Footer --}}
                    <div class="flex flex-row-reverse gap-3 pt-4 border-t border-stone-200 dark:border-slate-600">
                        <button wire:loading.remove wire:click="deleting({{ $deleteID }})" type="button" class="cursor-pointer inline-flex items-center rounded-sm px-4 py-2 bg-red-600 text-sm font-semibold text-white hover:bg-red-700 transition-none">
                            Yes, Delete
                        </button>
                        <button wire:loading.remove wire:click='closeDelete' type="button" class="cursor-pointer inline-flex rounded-sm border border-stone-300 dark:border-slate-600 px-4 py-2 bg-white dark:bg-slate-700 text-sm font-medium text-stone-700 dark:text-slate-200 transition-none hover:bg-stone-50 dark:hover:bg-slate-600">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
