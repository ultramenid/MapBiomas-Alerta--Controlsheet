<div x-data="auditModal()" x-effect="document.body.classList.toggle('overflow-hidden', open)" @keydown.escape.window="if(open) close()">
    <template x-teleport="body">
        <div x-show="open" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
            {{-- Backdrop --}}
            <div x-show="open" x-transition.opacity.duration.200ms class="fixed inset-0 bg-black/40" @click="close()"></div>

            {{-- Centering --}}
            <div class="flex min-h-screen items-center justify-center p-4">
                {{-- Panel --}}
                <div x-show="open" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" @click.stop class="relative w-full sm:max-w-5xl bg-white dark:bg-slate-800 rounded-sm shadow-xl">

                    {{-- Loading bar --}}
                    <div wire:loading class="absolute top-0 left-0 right-0 bg-red-400 dark:bg-red-900 py-1 animate-pulse text-xs text-white text-center rounded-t-sm">loading...</div>

                    <div class="px-5 py-5 max-h-[85vh] overflow-y-auto">
                        {{-- Header --}}
                        <div class="flex items-center justify-between mb-5 pb-4 border-b border-stone-200 dark:border-slate-600">
                            <div>
                                <h2 class="text-lg font-bold text-stone-900 dark:text-slate-100">{{ $alertId }}</h2>
                                <p class="text-sm text-stone-500 dark:text-slate-400 mt-1">{{ $observation }} - {{ $analis }}</p>
                            </div>
                            <button @click="close()" class="text-stone-400 hover:text-stone-600 dark:text-slate-400 dark:hover:text-slate-200 cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        {{-- Two-column layout --}}
                        <div class="flex sm:flex-row flex-col gap-6">
                            {{-- Left: Note --}}
                            <div class="sm:w-1/2 w-full">
                                <span class="text-label text-stone-500 dark:text-slate-400 mb-2 block">Note</span>
                                <template x-if="loading">
                                    <div class="space-y-3 animate-pulse mt-4">
                                        <div class="h-4 bg-gray-300 dark:bg-slate-600 rounded w-1/3"></div>
                                        <div class="h-4 bg-gray-300 dark:bg-slate-600 rounded w-full"></div>
                                        <div class="h-4 bg-gray-300 dark:bg-slate-600 rounded w-5/6"></div>
                                    </div>
                                </template>
                                <template x-if="!loading">
                                    <div class="bg-stone-50 dark:bg-slate-900 rounded-sm p-4 border border-stone-200 dark:border-slate-600 prose prose-sm dark:text-slate-300 max-h-[50vh] overflow-y-auto">
                                        {!! $alertNote !!}
                                    </div>
                                </template>
                            </div>

                            {{-- Right: Status + Reason --}}
                            <div class="sm:w-1/2 w-full">
                                {{-- Status select --}}
                                <div class="relative flex w-full flex-col mb-5">
                                    <label class="text-label text-stone-500 dark:text-slate-400 mb-2">Alert Status</label>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="absolute pointer-events-none right-4 top-9 size-5 text-stone-500 dark:text-slate-400">
                                        <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                                    </svg>
                                    <select wire:ignore wire:model='alertStatus' class="w-full appearance-none border border-stone-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-stone-900 dark:text-slate-100 px-4 py-2.5 text-sm rounded-sm focus:outline-none transition-none">
                                        <option value="pre-approved">Pre-Approved</option>
                                        <option value="refined">Refined</option>
                                        <option value="error">Error</option>
                                        <option value="rejected">Rejected</option>
                                        <option value="duplicate">Duplicate</option>
                                        <option value="approved">Approved</option>
                                        <option value="reexportimage">Re-export planet images</option>
                                        <option value="reclassification">Re-classification</option>
                                    </select>
                                </div>

                                {{-- Reason editor --}}
                                <div class="mb-5">
                                    <span class="text-label text-stone-500 dark:text-slate-400 mb-2 block">Reason</span>
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
                                                                if (content) editor.setContent(content);
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
                                        <textarea id="alertReason" name="alertReason" rows="1" required data-content="{{ $alertReason }}"></textarea>
                                    </div>
                                </div>

                                {{-- Footer --}}
                                <div class="flex flex-row-reverse gap-3 pt-4 border-t border-stone-200 dark:border-slate-600">
                                    <button @click="auditAlert()" :disabled="loading" wire:loading.attr="disabled" class="cursor-pointer inline-flex items-center rounded-sm px-4 py-2 bg-stone-900 dark:bg-slate-200 text-sm font-semibold text-white dark:text-stone-900 shadow-sm transition-none hover:bg-stone-800 dark:hover:bg-slate-300 disabled:opacity-50 disabled:cursor-not-allowed">
                                        <span wire:loading.remove wire:target="auditing">Audit Alert</span>
                                        <span wire:loading wire:target="auditing">Saving...</span>
                                    </button>
                                    <button @click="close()" type="button" class="cursor-pointer inline-flex rounded-sm border border-stone-300 dark:border-slate-600 px-4 py-2 bg-white dark:bg-slate-700 text-sm font-medium text-stone-700 dark:text-slate-200 transition-none hover:bg-stone-50 dark:hover:bg-slate-600">
                                        Close
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>

<script>
const AUDIT_ENDPOINT = '{{ $auditEndpoint ?? "/rest/audit" }}';

function auditModal() {
    return {
        open: false,
        loading: false,
        alertId: '',

        init() {
            window.addEventListener('open-audit-modal', (e) => {
                const id = e.detail.id;
                this.open = true;
                this.loading = true;

                fetch(`${AUDIT_ENDPOINT}/${id}`)
                    .then(res => res.json())
                    .then(data => this.fill(data))
                    .catch(() => { this.alertReason = 'Failed to load'; })
                    .finally(() => { this.loading = false; });
            });
        },

        fill(data) {
            this.alertId = data.alertId;
            this.$wire.set('alertId', data.alertId);
            this.$wire.set('alertStatus', data.auditorStatus);
            this.$wire.set('statusAlert', data.alertStatus);
            this.$wire.set('alertReason', data.auditorReason ?? null);
            this.$wire.set('observation', data.observation);
            this.$wire.set('analis', data.name);
            this.$wire.set('alertNote', data.alertNote ?? null);
        },

        close() {
            this.$wire.set('alertId', null);
            this.$wire.set('observation', null);
            this.$wire.set('analis', null);
            this.open = false;
        },

        auditAlert() {
            const id = this.alertId;
            if (!id) return;
            this.$wire.auditing(id);
        }
    }
}
</script>
