<div x-data="auditModal()" x-init="init()">

    <!-- MODAL -->
    <div
        class="fixed z-50 inset-0 overflow-y-auto ease-out duration-300"
        x-show="open"
        x-transition
        x-cloak

    >

        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity">
                <div class="absolute inset-0 bg-gray-100 dark:bg-gray-900 opacity-50"></div>
            </div>
            <!-- This element is to trick the browser into centering the modal contents. -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>​

            <div class=" px-4 py-6 inline-block align-bottom h-[660px] rounded-sm bg-white dark:bg-slate-700 text-left  shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-5xl w-full " role="dialog" aria-modal="true" aria-labelledby="modal-headline">
                <div wire:loading class="absolute flex w-[97%] justify-start text-center bg-red-300 py-1 animate-pulse text-xs  text-white mb-12" >loading. . .</div>

                <div class="w-full">
                    <a class="text-xl dark:text-slate-400">{{$alertId}} - {{$observation}} - {{$analis}}</a>
                </div>
                <div class=" flex sm:flex-row flex-col gap-6 ">
                    {{-- left side --}}
                    <div class="sm:w-6/12 w-full h-[580px] overflow-y-auto">
                        <div class="flex flex-col  mb-3 mt-4  ">
                             <a class="text-sm text-left prose dark:text-slate-400"><b>Note</b>:
                                <template x-if="loading">
                                    <div class="space-y-4 animate-pulse mt-4">
                                        <div class="h-4 bg-gray-300 dark:bg-slate-600 rounded w-1/3"></div>
                                        <div class="h-4 bg-gray-300 dark:bg-slate-600 rounded w-full"></div>
                                        <div class="h-4 bg-gray-300 dark:bg-slate-600 rounded w-5/6"></div>
                                    </div>
                                </template>

                                <template x-if="!loading">
                                    <div>
                                        {!! $alertNote !!}
                                    </div>
                                </template>
                            </a>
                        </div>
                    </div>
                    {{-- right side --}}
                    <div class="sm:w-6/12 w-full  ">
                        <label class="w-full"  >
                            <div class="relative flex w-full flex-col  text-neutral-600 dark:text-neutral-300">
                                <label for="os" class="w-fit pl-0.5 text-gray-700 mb-1 text-sm dark:text-slate-400">Alert Status</label>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="absolute pointer-events-none right-4 top-9 size-5">
                                    <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                                </svg>
                                <select wire:ignore wire:model='alertStatus' class="dark:bg-slate-600 dark:text-slate-300 dark:border-slate-700 w-full text-black appearance-none  border border-neutral-300 bg-gray-100 px-4 py-2 text-sm focus:outline-none">
                                    <option value="pre-approved">Pre-Approved</option>
                                    <option value="refined">Refined</option>
                                    <option value="error">Error</option>
                                    <option value="rejected">Rejected</option>
                                    <option value="duplicate">duplicate</option>
                                    <option value="approved">Approved</option>
                                    <option value="reexportimage">Re-export planet images</option>
                                    <option value="reclassification">Re-classification</option>

                                </select>
                            </div>
                        </label>

                        <div class="w-full  border-gray-300  mb-6 mt-6">
                            <h1 class="text-gray-700 dark:text-slate-400">Reason</h1>
                            <div
                            x-data="{
                                darkMode: document.documentElement.classList.contains('dark'),
                                editor: null,
                                initEditor() {
                                    const self = this;
                                    tinymce.init({
                                        selector: '#alertReason',
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
                                                    const content = document.getElementById('alertReason').dataset.content;
                                                    if (content) {
                                                        editor.setContent(content);
                                                    }
                                                }, 300);
                                            });
                                            editor.on('change blur', function() {
                                                @this.set('alertReason', editor.getContent());
                                            });
                                        }
                                    });
                                },
                                reinitEditor() {
                                    if (this.editor) {
                                        tinymce.remove('#alertReason');
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
                                id="alertReason"
                                name="alertReason"
                                rows="1"
                                required
                                data-content="{{ $alertReason }}"
                            ></textarea>
                        </div>
                        </div>
                        <div class=" px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse mt-6 mb-6 ">
                            <span class="flex w-full rounded-md shadow-sm sm:ml-3 sm:w-auto">
                                <button @click="auditAlert()" :disabled="loading"  wire:loading.attr="disabled" class="cursor-pointer inline-flex items-center justify-center gap-2 w-full rounded-md border border-transparent px-4 py-2 bg-black text-base font-medium text-gray-200 shadow-sm focus:outline-none transition sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed" >

                                    <!-- Text normal -->
                                    <span wire:loading.remove wire:target="auditing">
                                        Audit Alert
                                    </span>

                                    <!-- Text loading -->
                                    <span wire:loading wire:target="auditing">
                                        Saving...
                                    </span>

                                </button>

                            </span>
                            <span class="mt-3 flex w-full rounded-md shadow-sm sm:mt-0 sm:w-auto">

                                <button @click="close()" type="button" class=" cursor-pointer inline-flex justify-center w-full rounded-md border border-gray-300 px-4 py-2 bg-white text-base leading-6 font-medium text-gray-700 shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue transition ease-in-out duration-150 sm:text-sm sm:leading-5">
                                    Close
                                </button>

                            </span>
                        </div>
                    </div>



                </div>




            </div>
        </div>
    </div>

    <script>

function auditModal()
{
    return {

        open: false,
        loading: false,


        alertId: '',

        init()
        {
            window.addEventListener('open-audit-modal', (e) => {

                const id = e.detail.id;

                this.open = true;



                this.loading = true;

                fetch(`/rest/audit-test/${id}`)
                    .then(res => res.json())
                    .then(data => {

                        this.fill(data);

                    })
                    .catch(() => {

                        this.alertReason = 'Failed to load';

                    })
                    .finally(() => {

                        this.loading = false;

                    });

            });
        },

        clear(data){
            this.$wire.set('alertId', null);
            this.$wire.set('observation', null);
            this.$wire.set('analis', null);
        },

        fill(data)
        {

            this.alertId = data.alertId;
            this.$wire.set('alertId', data.alertId);
            this.$wire.set('alertStatus', data.auditorStatus);
            this.$wire.set('statusAlert', data.alertStatus);
            this.$wire.set('alertReason', data.auditorReason ?? null);
            this.$wire.set('observation', data.observation);
            this.$wire.set('analis', data.name);
            this.$wire.set('alertNote', data.alertNote ?? null);
        },

        close(){

            this.clear();
            this.open = false;
        },

        auditAlert(data)
        {
            const id = this.alertId;
            if (!id) return;

            // Jangan close dulu — biarkan server yang menentukan:
            // jika validasi gagal → PHP return early, modal tetap terbuka
            // jika berhasil → PHP redirect, halaman pindah otomatis
            this.$wire.auditing(id);
        }

    }
}

</script>
</div>
