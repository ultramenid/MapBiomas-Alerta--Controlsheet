<div class="max-w-3xl mx-auto py-12 px-4 z-20 relative bg-gray-50 mt-4">
    <div class="flex justify-between">
        <h1 class="font-semibold text-3xl mt-10 mb-6 text-gray-700">Edit alert</h1>
        <div class="flex justify-end items-center mt-4">
            <button wire:click='storeAlert' class="bg-black py-2 px-4 text-white w-32 cursor-pointer h-10">Update</button>
        </div>
    </div>

    <div class="mt-12 flex sm:flex-row flex-col gap-4">
        <div class="sm:w-6/12 w-full">
            <h1 class="text-gray-700 mb-1">Alert ID</h1>
            <input placeholder="0000000"  type="number" disabled  class=" text-sm bg-gray-300 border border-gray-200 text-gray-600  w-full  py-2 px-4 focus:outline-none  "  wire:model.defer='alertId' placeholder="">
        </div>
        <div class="sm:w-6/12 w-full">
            <h1 class="text-gray-700 mb-1">Observation</h1>
            <input placeholder="observation"  type="text" class=" text-sm bg-gray-100 border border-gray-200 text-gray-600  w-full  py-2 px-4 focus:outline-none  "  wire:model.defer='observation' placeholder="">
        </div>

    </div>
    <div class="mt-4 flex gap-4 sm:flex-row flex-col ">
        <div class="sm:w-6/12 w-full">
            <label class="w-full"  >
                <div class="relative flex w-full flex-col  text-neutral-600 dark:text-neutral-300">
                    <label for="os" class="w-fit pl-0.5 text-gray-700 mb-1">Alert Status</label>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="absolute pointer-events-none right-4 top-9 size-5">
                        <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                    </svg>
                    <select wire:ignore wire:model='alertStatus' class="w-full appearance-none text-black  border border-neutral-300 bg-gray-100 px-4 py-2 text-sm focus:outline-none">
                        <option selected>Please Select</option>
                        <option value="valid">Valid</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
            </label>
        </div>
        <div class="sm:w-6/12 w-full">
            <h1 class="text-gray-700 mb-1">Detection Date</h1>
            <div class="w-full" wire:ignore x-init="flatpickr('#tglkejadian', { enableTime: false,dateFormat: 'Y-m-d', disableMobile: 'true'});">
                <input id="tglkejadian" type="text" class="bg-gray-100  text-gray-00   w-full border border-gray-200  py-2 px-4 focus:outline-none  text-sm"  wire:model.defer='detectionDate' placeholder="Please select ">
            </div>
        </div>
    </div>
    <div class="mt-4 flex gap-4 sm:flex-row flex-col">
        <div class="sm:w-6/12 w-full" x-data="{open:false}" @click.away="open=false" @region.window="open = false">
            <h1 class=" text-gray-700  mb-1">Region</h1>
            <label class="w-full">
                <div  @click="open=true"   class="truncate w-full mb-2 bg-gray-100 cursor-pointer  text-gray-700  rounded text-sm  border py-2 px-4 focus:outline-none border-gray-300 dark:border-opacity-20" >{{$region}}</div>
            </label>


            <div style="display: none !important;" x-show="open" class="shadow px-2 py-2 flex flex-col   bg-black  z-20 absolute w-2/12"  >
                <input   wire:model.live='chooseRegion' type="text" name="" id="" class="w-full mb-4  bg-gray-100  text-gray-700  rounded   border  py-2 px-4 focus:outline-none border-gray-300 dark:border-opacity-20 text-sm" placeholder="type region">
                @foreach ($regions as $key => $value)
                    <a  wire:click="selectRegion('{{$value[0]}}')"  class="cursor-pointer text-white py-1 hover:bg-gray-700 px-4 text-sm">{{$value[0]}}</a>
                @endforeach
                    <a   class="text-white hover:bg-gray-700 px-4 text-xs text-center">...</a>
            </div>
        </div>
        <div class="sm:w-6/12 w-full" x-data="{open:false}" @click.away="open=false" @province.window="open = false">
            <h1 class=" text-gray-700  mb-1">Province</h1>
            <label class="w-full">
                <div  @click="open=true"   class="truncate w-full mb-2 bg-gray-100 cursor-pointer  text-gray-700  rounded text-sm  border py-2 px-4 focus:outline-none border-gray-300 dark:border-opacity-20" >{{$province}}</div>
            </label>


            <div style="display: none !important;" x-show="open" class="shadow px-2 py-2 flex flex-col   bg-black  z-20 absolute w-2/12"  >
                <input   wire:model.live='chooseProvince' type="text" name="" id="" class="w-full mb-4  bg-gray-100  text-gray-700  rounded   border  py-2 px-4 focus:outline-none border-gray-300 dark:border-opacity-20 text-sm" placeholder="type province">
                @foreach ($provincies as $key => $value)
                    <a  wire:click="selectProvince('{{$value[0]}}')"  class="cursor-pointer text-white py-1 hover:bg-gray-700 px-4 text-sm">{{$value[0]}}</a>
                @endforeach
                    <a   class="text-white hover:bg-gray-700 px-4 text-xs text-center">...</a>

            </div>
        </div>
    </div>

    <div class="mt-4 flex gap-4 sm:flex-row flex-col ">
        <div class="w-full">
            <label class="w-full"  >
                <div class="relative flex w-full flex-col  text-neutral-600 dark:text-neutral-300">
                    <label for="os" class="w-fit pl-0.5 text-gray-700 mb-1 font-semibold">Platform Status</label>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="absolute pointer-events-none right-4 top-9 size-5">
                        <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                    </svg>
                    <select wire:ignore wire:model='platformStatus' class="w-full appearance-none text-black  border border-neutral-300 bg-gray-100 px-4 py-2 text-sm focus:outline-none">
                        <option >Please select</option>
                        <option value="sccon">Sccon</option>
                        <option value="workspace">Workspace</option>
                    </select>
                </div>
            </label>
        </div>

    </div>

    <div class="w-full  border-gray-300  mb-6 mt-6">
        <h1 class="text-gray-700 ">Note</h1>
        <div class="w-full  border-transparent focus:border-transparent focus:ring-0 "
            wire:ignore
            x-init="
            tinymce.init({
                selector: '#alertNote',
                height : '30vh',
                promotion: false,
                branding: false,
                license_key: 'gpl',
                relative_urls : false,
                    remove_script_host : false,
                    convert_urls : true,
                    highlight_on_focus: false,
                    content_style: 'body {  background-color: #f4f5f7; font-size: 14px; }, ',
                    plugins:
                        'lists advlist autolink  link image  charmap    anchor pagebreak searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking table emoticons template  help',

                        toolbar: ' fullscreen image  bullist numlist   | ',
                        menu: {
                        favs: {title: 'My Favorites', items: 'code visualaid | searchreplace | emoticons'}
                        },
                        menubar: ' ',
                        file_picker_callback : function(callback, value, meta) {
                            var x = window.innerWidth || document.documentElement.clientWidth || document.getElementsByTagName('body')[0].clientWidth;
                            var y = window.innerHeight|| document.documentElement.clientHeight|| document.getElementsByTagName('body')[0].clientHeight;
                            var cmsURL = '/cms/' + 'controlsheet-filemanager?editor=' + meta.fieldname;
                            if (meta.filetype == 'image') {
                                cmsURL = cmsURL + '&type=Images';
                            } else {
                                cmsURL = cmsURL + '&type=Files';
                            }
                            tinyMCE.activeEditor.windowManager.openUrl({
                                url : cmsURL,
                                title : 'Filemanager',
                                width : x * 0.8,
                                height : y * 0.8,
                                resizable : 'yes',
                                close_previous : 'no',
                                onMessage: (api, message) => {
                                callback(message.content);
                                }
                            });
                        },
                        setup: function(editor) {
                            editor.on('change', function(e) {
                                @this.set('alertNote', editor.getContent());
                        });
                    }
            });">
            <textarea  rows="1" id="alertNote" name="alertNote"  wire:model.defer='alertNote' required></textarea>
        </div>
    </div>

</div>
