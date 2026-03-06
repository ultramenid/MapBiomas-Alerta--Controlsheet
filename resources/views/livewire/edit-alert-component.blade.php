<div class="max-w-3xl mx-auto py-12 px-4 z-20 relative bg-gray-50 dark:bg-slate-800 mt-4">
    <div class="flex justify-between">
        <h1 class="font-semibold text-3xl mt-10 mb-6 text-gray-700 dark:text-slate-200">Edit alert</h1>
        <div class="flex justify-end items-center mt-4">
            <button wire:click='storeAlert' wire:loading.attr='disabled' class="relative bg-black dark:bg-slate-600 py-2 px-4 text-white w-full cursor-pointer h-10 hover:opacity-80 transition disabled:opacity-60 disabled:cursor-not-allowed">
                <span wire:loading.remove wire:target='storeAlert'>Update</span>
                <span wire:loading wire:target='storeAlert' class="flex items-center justify-center gap-2">

                    Updating alert...
                </span>
            </button>
        </div>
    </div>

    {{-- Row 1: Alert ID & Observation --}}
    <div class="mt-12 flex sm:flex-row flex-col gap-4">
        <div class="sm:w-6/12 w-full">
            <h1 class="text-gray-700 dark:text-slate-300 mb-1">Alert ID</h1>
            <input
                type="number"
                disabled
                placeholder="0000000"
                class="text-sm bg-gray-300 dark:bg-slate-600 border border-gray-200 dark:border-slate-500 text-gray-600 dark:text-slate-300 w-full py-2 px-4 focus:outline-none"
                wire:model.defer='alertId'
            >
        </div>
        <div class="sm:w-6/12 w-full">
            <h1 class="text-gray-700 dark:text-slate-300 mb-1">Observation</h1>
            <input
                type="text"
                placeholder="Observation"
                class="text-sm bg-gray-100 dark:bg-slate-700 border border-gray-200 dark:border-slate-500 text-gray-600 dark:text-slate-300 w-full py-2 px-4 focus:outline-none"
                wire:model.defer='observation'
            >
        </div>
    </div>

    {{-- Row 2: Alert Status & Detection Date --}}
    <div class="mt-4 flex gap-4 sm:flex-row flex-col">
        <div class="sm:w-6/12 w-full">
            <div class="relative flex w-full flex-col text-neutral-600 dark:text-neutral-300">
                <label for="alertStatus" class="text-gray-700 dark:text-slate-300 mb-1">Alert Status</label>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                    class="absolute pointer-events-none right-4 top-9 size-5 dark:text-slate-300">
                    <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                </svg>
                <select
                    id="alertStatus"
                    wire:model='alertStatus'
                    class="w-full appearance-none text-black dark:text-slate-200 border border-neutral-300 dark:border-slate-500 bg-gray-100 dark:bg-slate-700 px-4 py-2 text-sm focus:outline-none"
                >
                    <option>Please Select</option>
                    <option value="valid">Valid</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
        </div>
        <div class="sm:w-6/12 w-full">
            <h1 class="text-gray-700 dark:text-slate-300 mb-1">Detection Date</h1>
            <div class="w-full" wire:ignore x-init="flatpickr('#tglkejadian', { enableTime: false, dateFormat: 'Y-m-d', disableMobile: true })">
                <input
                    id="tglkejadian"
                    type="text"
                    placeholder="Please select"
                    class="bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-slate-300 w-full border border-gray-200 dark:border-slate-500 py-2 px-4 focus:outline-none text-sm"
                    wire:model.defer='detectionDate'
                >
            </div>
        </div>
    </div>

    {{-- Row 3: Region & Province --}}
    <div class="mt-4 flex gap-4 sm:flex-row flex-col">
        {{-- Region --}}
        <div class="sm:w-6/12 w-full" x-data="{ open: false }" @click.away="open = false" @region.window="open = false">
            <h1 class="text-gray-700 dark:text-slate-300 mb-1">Region</h1>
            <div
                @click="open = true"
                class="truncate w-full mb-2 bg-gray-100 dark:bg-slate-700 cursor-pointer text-gray-700 dark:text-slate-300 rounded text-sm border border-gray-300 dark:border-slate-500 py-2 px-4 focus:outline-none"
            >{{ $region }}</div>

            <div
                x-show="open"
                x-transition
                class="shadow px-2 py-2 flex flex-col bg-black dark:bg-slate-900 z-20 absolute w-2/12"
            >
                <input
                    wire:model.live='chooseRegion'
                    type="text"
                    placeholder="Type region"
                    class="w-full mb-4 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-slate-300 rounded border border-gray-300 dark:border-slate-500 py-2 px-4 focus:outline-none text-sm"
                >
                @foreach ($regions as $value)
                    <a wire:click="selectRegion('{{ $value[0] }}')" class="cursor-pointer text-white py-1 hover:bg-gray-700 px-4 text-sm">{{ $value[0] }}</a>
                @endforeach
                <a class="text-white hover:bg-gray-700 px-4 text-xs text-center">...</a>
            </div>
        </div>

        {{-- Province --}}
        <div class="sm:w-6/12 w-full" x-data="{ open: false }" @click.away="open = false" @province.window="open = false">
            <h1 class="text-gray-700 dark:text-slate-300 mb-1">Province</h1>
            <div
                @click="open = true"
                class="truncate w-full mb-2 bg-gray-100 dark:bg-slate-700 cursor-pointer text-gray-700 dark:text-slate-300 rounded text-sm border border-gray-300 dark:border-slate-500 py-2 px-4 focus:outline-none"
            >{{ $province }}</div>

            <div
                x-show="open"
                x-transition
                class="shadow px-2 py-2 flex flex-col bg-black dark:bg-slate-900 z-20 absolute w-2/12"
            >
                <input
                    wire:model.live='chooseProvince'
                    type="text"
                    placeholder="Type province"
                    class="w-full mb-4 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-slate-300 rounded border border-gray-300 dark:border-slate-500 py-2 px-4 focus:outline-none text-sm"
                >
                @foreach ($provincies as $value)
                    <a wire:click="selectProvince('{{ $value[0] }}')" class="cursor-pointer text-white py-1 hover:bg-gray-700 px-4 text-sm">{{ $value[0] }}</a>
                @endforeach
                <a class="text-white hover:bg-gray-700 px-4 text-xs text-center">...</a>
            </div>
        </div>
    </div>

    {{-- Row 4: Platform Status --}}
    <div class="mt-4 flex gap-4 sm:flex-row flex-col">
        <div class="w-full">
            <div class="relative flex w-full flex-col text-neutral-600 dark:text-neutral-300">
                <label for="platformStatus" class="text-gray-700 dark:text-slate-300 mb-1 font-semibold">Platform Status</label>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                    class="absolute pointer-events-none right-4 top-9 size-5 dark:text-slate-300">
                    <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                </svg>
                <select
                    id="platformStatus"
                    wire:model='platformStatus'
                    class="w-full appearance-none text-black dark:text-slate-200 border border-neutral-300 dark:border-slate-500 bg-gray-100 dark:bg-slate-700 px-4 py-2 text-sm focus:outline-none"
                >
                    <option>Please select</option>
                    <option value="sccon">Sccon</option>
                    <option value="workspace">Workspace</option>
                </select>
            </div>
        </div>
    </div>

    {{-- TinyMCE Note --}}
    <div class="w-full border-gray-300 dark:border-slate-600 mb-6 mt-6">
        <h1 class="text-gray-700 dark:text-slate-300 mb-1">Note</h1>
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
                            editor.on('init', function() {
                                setTimeout(function() {
                                    const content = document.getElementById('alertNote').dataset.content;
                                    if (content) {
                                        editor.setContent(content);
                                    }
                                }, 300);
                            });
                            editor.on('change blur', function() {
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
            class="w-full"
        >
            <textarea
                id="alertNote"
                name="alertNote"
                rows="1"
                required
                data-content="{{ $alertNote }}"
            ></textarea>
        </div>
    </div>

</div>
