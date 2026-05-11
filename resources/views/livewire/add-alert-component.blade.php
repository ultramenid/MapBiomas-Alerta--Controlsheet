<div class="glass rounded-sm p-5 mb-5 max-w-3xl mx-auto relative z-20 dark:text-slate-400">
    <div class="flex justify-between">
        <h1 class="text-label text-stone-900 dark:text-slate-100 mt-10 mb-6">Add alert</h1>
        <div class="flex justify-end items-center mt-4">
            <button wire:click='storeAlert' class="bg-stone-900 dark:bg-slate-200 text-white dark:text-stone-900 py-2 px-4 text-sm font-semibold rounded-sm cursor-pointer hover:bg-stone-800 dark:hover:bg-slate-300 transition-none">Save</button>
        </div>
    </div>

    <div class="mt-12 flex sm:flex-row flex-col gap-4">
        <div class="sm:w-6/12 w-full">
            <h1 class="text-label text-stone-600 dark:text-slate-400 mb-1">Alert ID</h1>
            <input placeholder="0000000"  type="number"  class="bg-white dark:bg-slate-800 border border-stone-300 dark:border-slate-600 text-stone-900 dark:text-slate-100 w-full rounded-sm px-3 py-2 text-sm focus:outline-none transition-none"  wire:model.defer='alertId' placeholder="">
        </div>
        <div class="sm:w-6/12 w-full">
            <h1 class="text-label text-stone-600 dark:text-slate-400 mb-1">Observation</h1>
            <input placeholder="observation"  type="text" class="bg-white dark:bg-slate-800 border border-stone-300 dark:border-slate-600 text-stone-900 dark:text-slate-100 w-full rounded-sm px-3 py-2 text-sm focus:outline-none transition-none"  wire:model.defer='observation' placeholder="">
        </div>

    </div>
    <div class="mt-4 flex gap-4 sm:flex-row flex-col ">
        <div class="sm:w-6/12 w-full">
            <label class="w-full"  >
                <div class="relative flex w-full flex-col  text-neutral-600 dark:text-neutral-300 ">
                    <label for="os" class="w-fit pl-0.5 text-stone-700 dark:text-slate-300 mb-1 dark:text-slate-400">Alert Status</label>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="absolute pointer-events-none right-4 top-9 size-5">
                        <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                    </svg>
                    <select wire:ignore wire:model='alertStatus' class="dark:bg-slate-700 dark:border-slate-700 dark:text-slate-400 w-full appearance-none text-black  border border-stone-300 dark:border-slate-600 bg-stone-100 dark:bg-slate-800 px-4 py-2 text-sm focus:outline-none">
                        <option selected>Please Select</option>
                        <option value="valid">Valid</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
            </label>
        </div>
        <div class="sm:w-6/12 w-full">
            <h1 class="text-label text-stone-600 dark:text-slate-400 mb-1">Detection Date</h1>
            <div class="w-full" wire:ignore x-init="flatpickr('#tglkejadian', { enableTime: false,dateFormat: 'Y-m-d', disableMobile: 'true'});">
                <input id="tglkejadian" type="text" class="bg-white dark:bg-slate-800 border border-stone-300 dark:border-slate-600 text-stone-900 dark:text-slate-100 w-full rounded-sm px-3 py-2 text-sm focus:outline-none transition-none"  wire:model.defer='detectionDate' placeholder="Please select ">
            </div>
        </div>
    </div>
    <div class="mt-4 flex gap-4 sm:flex-row flex-col">
        <div class="sm:w-6/12 w-full" x-data="{open:false}" @click.away="open=false" @region.window="open = false">
            <h1 class="text-label text-stone-600 dark:text-slate-400 mb-1">Region</h1>
            <label class="w-full">
                <div  @click="open=true"   class="bg-white dark:bg-slate-800 border border-stone-300 dark:border-slate-600 text-stone-900 dark:text-slate-100 w-full rounded-sm px-3 py-2 text-sm focus:outline-none transition-none cursor-pointer truncate mb-2" >{{$region}}</div>
            </label>


            <div style="display: none !important;" x-show="open" class="shadow px-2 py-2 flex flex-col   bg-black  z-20 absolute w-4/12"  >
                <input   wire:model.live='chooseRegion' type="text" name="" id="" class="bg-white dark:bg-slate-800 border border-stone-300 dark:border-slate-600 text-stone-900 dark:text-slate-100 w-full rounded-sm px-3 py-2 text-sm focus:outline-none transition-none mb-4" placeholder="type region">
                @foreach ($regions as $key => $value)
                    <a  wire:click="selectRegion('{{$value[0]}}')"  class="cursor-pointer text-white py-1 hover:bg-stone-800 dark:hover:bg-slate-600 px-4 text-sm">{{$value[0]}}</a>
                @endforeach
                    <a   class="text-white hover:bg-stone-800 dark:hover:bg-slate-600 px-4 text-xs text-center">...</a>
            </div>
        </div>
        <div class="sm:w-6/12 w-full" x-data="{open:false}" @click.away="open=false" @province.window="open = false">
            <h1 class="text-label text-stone-600 dark:text-slate-400 mb-1">Province</h1>
            <label class="w-full">
                <div  @click="open=true"   class="bg-white dark:bg-slate-800 border border-stone-300 dark:border-slate-600 text-stone-900 dark:text-slate-100 w-full rounded-sm px-3 py-2 text-sm focus:outline-none transition-none cursor-pointer truncate mb-2" >{{$province}}</div>
            </label>


            <div style="display: none !important;" x-show="open" class="shadow px-2 py-2 flex flex-col   bg-black  z-20 absolute w-4/12"  >
                <input   wire:model.live='chooseProvince' type="text" name="" id="" class="bg-white dark:bg-slate-800 border border-stone-300 dark:border-slate-600 text-stone-900 dark:text-slate-100 w-full rounded-sm px-3 py-2 text-sm focus:outline-none transition-none mb-4" placeholder="type province">
                @foreach ($provincies as $key => $value)
                    <a  wire:click="selectProvince('{{$value[0]}}')"  class="cursor-pointer text-white py-1 hover:bg-stone-800 dark:hover:bg-slate-600 px-4 text-sm">{{$value[0]}}</a>
                @endforeach
                    <a   class="text-white hover:bg-stone-800 dark:hover:bg-slate-600 px-4 text-xs text-center">...</a>

            </div>
        </div>
    </div>

    <div class="w-full  border-stone-300 dark:border-slate-700  mb-6 mt-6 ">
        <h1 class="text-label text-stone-600 dark:text-slate-400">Note</h1>
        <div
            x-data="{
                darkMode: document.documentElement.classList.contains('dark'),
                editor: null,
                initEditor() {
                    const self = this;
                    tinymce.init({
                        selector: '#alertNote',
                        height: '30vh',
                        promotion: false,
                        branding: false,
                        license_key: 'gpl',
                        relative_urls: false,
                        remove_script_host: false,
                        convert_urls: true,
                        highlight_on_focus: false,
                        skin: self.darkMode ? 'oxide-dark' : 'oxide',
                        content_css: self.darkMode ? 'dark' : 'default',
                        content_style: self.darkMode
                            ? 'body { background-color: #1e293b; color: #cbd5e1; font-size: 14px; }'
                            : 'body { background-color: #f4f5f7; font-size: 14px; }',
                        plugins: 'lists advlist autolink link image charmap anchor pagebreak searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking table emoticons help',
                        toolbar: 'fullscreen image bullist numlist |',
                        menubar: false,
                        file_picker_callback: function(callback, value, meta) {
                            var x = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
                            var y = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
                            var cmsURL = '/cms/controlsheet-filemanager?editor=' + meta.fieldname;
                            cmsURL += meta.filetype === 'image' ? '&type=Images' : '&type=Files';
                            tinyMCE.activeEditor.windowManager.openUrl({
                                url: cmsURL,
                                title: 'Filemanager',
                                width: x * 0.8,
                                height: y * 0.8,
                                resizable: true,
                                close_previous: false,
                                onMessage: (api, message) => callback(message.content)
                            });
                        },
                        setup: function(editor) {
                            self.editor = editor;
                            editor.on('change blur input', function() {
                                @this.set('alertNote', editor.getContent());
                            });
                        }
                    });
                },
                reinitEditor() {
                    if (this.editor) {
                        tinymce.remove('#alertNote');
                        this.editor = null;
                    }
                    this.$nextTick(() => this.initEditor());
                }
            }"
            x-init="
                initEditor();
                // Watch for dark mode class changes on <html>
                const observer = new MutationObserver(() => {
                    const isDark = document.documentElement.classList.contains('dark');
                    if (isDark !== darkMode) {
                        darkMode = isDark;
                        reinitEditor();
                    }
                });
                observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
            "
            wire:ignore
            class="w-full rounded-sm "
        >
            <textarea id="alertNote" name="alertNote" rows="1" required></textarea>
        </div>

    </div>

</div>
