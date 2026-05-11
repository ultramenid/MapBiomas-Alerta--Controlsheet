<div class="w-full">
    <h1 class="text-label text-stone-900 dark:text-slate-100">Change Password</h1>

    <div class="w-full gap-4 flex sm:flex-row flex-col mt-6">
        <div class="sm:w-6/12 w-full">
            <h1 class="text-label text-stone-600 dark:text-slate-400 mb-1">Old password</h1>
            <input placeholder="***"  type="password"  class="bg-white dark:bg-slate-800 border border-stone-300 dark:border-slate-600 text-stone-900 dark:text-slate-100 w-full rounded-sm px-3 py-2 text-sm focus:outline-none transition-none"  wire:model.defer='oldpassword' placeholder="">
        </div>
        <div class="sm:w-6/12 w-full">
            <h1 class="text-label text-stone-600 dark:text-slate-400 mb-1">New Password</h1>
            <input placeholder="***"  type="password" class="bg-white dark:bg-slate-800 border border-stone-300 dark:border-slate-600 text-stone-900 dark:text-slate-100 w-full rounded-sm px-3 py-2 text-sm focus:outline-none transition-none"  wire:model.defer='newpassword' placeholder="">
        </div>
    </div>
    <div class="w-full gap-4 flex sm:flex-row flex-col mt-2 sm:justify-end">

        <div class="sm:w-6/12 w-full px-2">
            <h1 class="text-label text-stone-600 dark:text-slate-400 mb-1">Confirm Password</h1>
            <input placeholder="***"  type="password" class="bg-white dark:bg-slate-800 border border-stone-300 dark:border-slate-600 text-stone-900 dark:text-slate-100 w-full rounded-sm px-3 py-2 text-sm focus:outline-none transition-none"  wire:model.defer='confirmpassword' placeholder="">
        </div>
    </div>

    <div class="w-full gap-4 flex sm:flex-row flex-col mt-12 sm:justify-end">
        <button wire:click='storePassword' class="bg-stone-900 dark:bg-slate-200 text-white dark:text-stone-900 py-2 px-4 text-sm font-semibold rounded-sm cursor-pointer hover:bg-stone-800 dark:hover:bg-slate-300 transition-none w-full sm:w-auto">Update</button>
    </div>

</div>
