<div>
    @if ($deleter)
    <div class="fixed z-50 inset-0 overflow-y-auto ease-out duration-400"  x-show="open" x-transition x-cloak style="display: none !important">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity">
                <div class="absolute inset-0 bg-gray-100 dark:bg-gray-900 opacity-50"></div>
            </div>
            <!-- This element is to trick the browser into centering the modal contents. -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>​

            <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-sm text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full " role="dialog" aria-modal="true" aria-labelledby="modal-headline">
                <div class="px-6 py-8">
                    <div class="flex items-center justify-center mb-6">
                        <div class="w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-red-600 dark:text-red-400">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                        </div>
                    </div>
                    <h1 class="text-center text-xl font-bold text-stone-900 dark:text-slate-100 mb-2">Delete Alert</h1>
                    <p class="text-center text-sm text-stone-500 dark:text-slate-400 mb-8">Are you sure you want to delete alert <span class="font-semibold text-stone-900 dark:text-slate-200">{{$alertDeleteId}}</span>?</p>

                    <div class="flex gap-3 justify-center">
                        <span class="flex rounded-md shadow-sm">
                            <button wire:loading.remove wire:click="deleting({{ $alertDeleteId }})" type="button" class="inline-flex justify-center rounded-sm border border-transparent px-6 py-2.5 bg-red-600 text-sm font-semibold text-white shadow-sm hover:bg-red-700 focus:outline-none cursor-pointer transition-none">
                                Yes, Delete
                            </button>
                        </span>
                        <span class="flex rounded-md shadow-sm">
                            <button wire:loading.remove wire:click='closeDelete' type="button" class="inline-flex justify-center rounded-sm border border-stone-300 dark:border-slate-600 px-6 py-2.5 bg-white dark:bg-slate-700 text-sm font-semibold text-stone-700 dark:text-slate-200 shadow-sm hover:bg-stone-50 dark:hover:bg-slate-600 focus:outline-none cursor-pointer transition-none">
                                Cancel
                            </button>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>