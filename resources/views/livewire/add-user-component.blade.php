<div class="max-w-3xl mx-auto py-12 px-4 bg-gray-50 mt-4">
    <div class="flex justify-between">
        <h1 class="font-semibold text-3xl mt-10 mb-6 text-gray-700">Add user</h1>
        <div class="flex justify-end items-center mt-4">
            <button wire:click='storeUser' class="bg-black py-2 px-4 text-white w-32 cursor-pointer h-10">Save</button>
        </div>
    </div>

    <div class="mt-12 flex sm:flex-row flex-col gap-4">
        <div class="sm:w-6/12 w-full">
            <h1 class="text-gray-700 mb-1">Name</h1>
            <input placeholder="name please"  type="text"  class=" text-sm bg-gray-100 border border-gray-200 text-gray-600  w-full  py-2 px-4 focus:outline-none  "  wire:model.defer='name' placeholder="">
        </div>
        <div class="sm:w-6/12 w-full">
            <h1 class="text-gray-700 mb-1">Email</h1>
            <input placeholder="user@email.com"  type="email" class=" text-sm bg-gray-100 border border-gray-200 text-gray-600  w-full  py-2 px-4 focus:outline-none  "  wire:model.defer='email' placeholder="">
        </div>

    </div>
    <div class="mt-6 flex sm:flex-row flex-col gap-4">
        <div class="sm:w-6/12 w-full">
            <h1 class="text-gray-700 mb-1">Contact</h1>
            <input placeholder="08?"  type="text"  class=" text-sm bg-gray-100 border border-gray-200 text-gray-600  w-full  py-2 px-4 focus:outline-none  "  wire:model.defer='contact' placeholder="">
        </div>
        <div class="sm:w-6/12 w-full">
            <h1 class="text-gray-700 mb-1">Password</h1>
            <input placeholder="****"  type="password" class=" text-sm bg-gray-100 border border-gray-200 text-gray-600  w-full  py-2 px-4 focus:outline-none  "  wire:model.defer='password' placeholder="">
        </div>

    </div>

    <div class="mt-6">
        <div class="sm:w-6/12 w-full">
            <label class="w-full"  >
                <div class="relative flex w-full flex-col  text-neutral-600 dark:text-neutral-300">
                    <label for="os" class="w-fit pl-0.5 text-gray-700 mb-1">Level</label>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="absolute pointer-events-none right-4 top-9 size-5">
                        <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                    </svg>
                    <select wire:ignore wire:model='level' class="w-full appearance-none  border border-neutral-300 bg-gray-100 px-4 py-2 text-sm focus:outline-none">
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
