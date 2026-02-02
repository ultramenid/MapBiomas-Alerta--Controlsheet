<div class="w-full border border-gray-300 dark:border-opacity-20 rounded px-6 py-6 ">
    <h1 class="text-2xl font-semibold  text-newbg-newgray-900 dark:text-gray-300"></h1>

    <div class="w-full gap-4 flex sm:flex-row flex-col mt-6">
        <div class="sm:w-6/12 w-full">
            <h1 class="text-gray-700 mb-1">Old password</h1>
            <input placeholder="***"  type="password"  class=" text-sm bg-gray-100 border border-gray-200 text-gray-600  w-full  py-2 px-4 focus:outline-none  "  wire:model.defer='oldpassword' placeholder="">
        </div>
        <div class="sm:w-6/12 w-full">
            <h1 class="text-gray-700 mb-1">New Password</h1>
            <input placeholder="***"  type="password" class=" text-sm bg-gray-100 border border-gray-200 text-gray-600  w-full  py-2 px-4 focus:outline-none  "  wire:model.defer='newpassword' placeholder="">
        </div>
    </div>
    <div class="w-full gap-4 flex sm:flex-row flex-col mt-2 sm:justify-end">

        <div class="sm:w-6/12 w-full px-2">
            <h1 class="text-gray-700 mb-1">Confirm Password</h1>
            <input placeholder="***"  type="password" class=" text-sm bg-gray-100 border border-gray-200 text-gray-600  w-full  py-2 px-4 focus:outline-none  "  wire:model.defer='confirmpassword' placeholder="">
        </div>
    </div>

    <div class="w-full gap-4 flex sm:flex-row flex-col mt-12 sm:justify-end">
        <button wire:click='storePassword' class="bg-black px-4 py-2 text-white cursor-pointer sm:w-2/12 w-full">Update</button>
    </div>

</div>
