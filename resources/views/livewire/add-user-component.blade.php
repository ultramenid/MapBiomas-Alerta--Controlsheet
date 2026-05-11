<div class="glass rounded-sm p-5 mb-5 max-w-3xl mx-auto relative z-20 dark:text-slate-400">
    <div class="flex justify-between">
        <h1 class="text-label text-stone-900 dark:text-slate-100 mt-10 mb-6">Add user</h1>
        <div class="flex justify-end items-center mt-4">
            <button wire:click='storeUser' class="bg-stone-900 dark:bg-slate-200 text-white dark:text-stone-900 py-2 px-4 text-sm font-semibold rounded-sm cursor-pointer hover:bg-stone-800 dark:hover:bg-slate-300 transition-none">Save</button>
        </div>
    </div>

    <div class="mt-12 flex sm:flex-row flex-col gap-4">
        <div class="sm:w-6/12 w-full">
            <h1 class="text-label text-stone-600 dark:text-slate-400 mb-1">Name</h1>
            <input placeholder="name please"  type="text"  class="bg-white dark:bg-slate-900 border border-stone-300 dark:border-slate-600 text-stone-900 dark:text-slate-100 w-full rounded-sm px-3 py-2 text-sm focus:outline-none transition-none"  wire:model.defer='name' placeholder="">
        </div>
        <div class="sm:w-6/12 w-full">
            <h1 class="text-label text-stone-600 dark:text-slate-400 mb-1">Email</h1>
            <input placeholder="user@email.com"  type="email" class="bg-white dark:bg-slate-900 border border-stone-300 dark:border-slate-600 text-stone-900 dark:text-slate-100 w-full rounded-sm px-3 py-2 text-sm focus:outline-none transition-none"  wire:model.defer='email' placeholder="">
        </div>

    </div>
    <div class="mt-6 flex sm:flex-row flex-col gap-4">
        <div class="sm:w-6/12 w-full">
            <h1 class="text-label text-stone-600 dark:text-slate-400 mb-1">Contact</h1>
            <input placeholder="08?"  type="text"  class="bg-white dark:bg-slate-900 border border-stone-300 dark:border-slate-600 text-stone-900 dark:text-slate-100 w-full rounded-sm px-3 py-2 text-sm focus:outline-none transition-none"  wire:model.defer='contact' placeholder="">
        </div>
        <div class="sm:w-6/12 w-full">
            <h1 class="text-label text-stone-600 dark:text-slate-400 mb-1">Password</h1>
            <input placeholder="****"  type="password" class="bg-white dark:bg-slate-900 border border-stone-300 dark:border-slate-600 text-stone-900 dark:text-slate-100 w-full rounded-sm px-3 py-2 text-sm focus:outline-none transition-none"  wire:model.defer='password' placeholder="">
        </div>

    </div>

    <div class="mt-6">
        <div class="sm:w-6/12 w-full">
            <label class="w-full"  >
                <div class="relative flex w-full flex-col  text-neutral-600 dark:text-slate-400">
                    <label for="os" class="w-fit pl-0.5 text-stone-700 dark:text-slate-300 mb-1 dark:text-slate-400">Level</label>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="absolute pointer-events-none right-4 top-9 size-5">
                        <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                    </svg>
                    <select wire:ignore wire:model='level' class="w-full appearance-none bg-white dark:bg-slate-900 border border-stone-300 dark:border-slate-600 text-stone-900 dark:text-slate-100 px-3 py-2 text-sm rounded-sm focus:outline-none cursor-pointer transition-none">
                        <option selected>Please Select</option>
                        <option value="2">Validator</option>
                        <option value="1">Auditor</option>
                        <option value="0">Admin</option>

                    </select>
                </div>
            </label>
        </div>
    </div>


</div>
