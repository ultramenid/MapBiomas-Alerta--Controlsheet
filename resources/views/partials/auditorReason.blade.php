<div x-data="reasonModal()" x-effect="document.body.classList.toggle('overflow-hidden', open)" @keydown.escape.window="if(open) close()">
    <template x-teleport="body">
        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            {{-- Backdrop --}}
            <div x-show="open" x-transition.opacity.duration.200ms class="fixed inset-0 bg-black/40" @click="close()"></div>

            {{-- Panel --}}
            <div x-show="open" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" @click.stop class="relative w-full sm:max-w-lg bg-white dark:bg-slate-800 rounded-sm shadow-xl">
                <div class="px-5 py-5">
                    {{-- Header --}}
                    <div class="flex items-center justify-between mb-5 pb-4 border-b border-stone-200 dark:border-slate-600">
                        <div>
                            <h2 class="text-lg font-bold text-stone-900 dark:text-slate-100" x-text="alertId"></h2>
                            <span class="inline-flex items-center mt-2 rounded-sm text-xs font-semibold uppercase tracking-wider bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-700 px-3 py-1.5" x-text="alertStatus"></span>
                        </div>
                        <button @click="close()" class="text-stone-400 hover:text-stone-600 dark:text-slate-400 dark:hover:text-slate-200 cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Body --}}
                    <template x-if="loading">
                        <div class="animate-pulse text-gray-400 py-8 text-center text-sm">Memuat data...</div>
                    </template>
                    <template x-if="!loading">
                        <div class="bg-stone-50 dark:bg-slate-900 rounded-sm p-4 border border-stone-200 dark:border-slate-600 max-h-[50vh] overflow-y-auto">
                            <span class="text-label text-stone-500 dark:text-slate-400 mb-2 block">Reason</span>
                            <p class="text-sm dark:text-slate-300" x-html="alertReason"></p>
                        </div>
                    </template>

                    {{-- Footer --}}
                    <div class="flex flex-row-reverse gap-3 mt-5 pt-4 border-t border-stone-200 dark:border-slate-600">
                        <button @click="fixAlert()" :disabled="loading" class="cursor-pointer inline-flex items-center rounded-sm px-4 py-2 bg-stone-900 dark:bg-slate-200 text-white dark:text-stone-900 text-sm font-semibold hover:bg-stone-800 dark:hover:bg-slate-300 disabled:opacity-50 transition-none">
                            Fix Alert
                        </button>
                        <button @click="close()" class="cursor-pointer inline-flex rounded-sm border border-stone-300 dark:border-slate-600 px-4 py-2 bg-white dark:bg-slate-700 text-sm font-medium text-stone-700 dark:text-slate-200 transition-none hover:bg-stone-50 dark:hover:bg-slate-600">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>

<script>
function reasonModal() {
    return {
        open: false,
        loading: false,
        alertId: '',
        alertStatus: '',
        alertReason: '',

        init() {
            window.addEventListener('open-reason-modal', (e) => {
                const id = e.detail.id;
                this.open = true;
                this.loading = true;

                fetch(`/rest/fix/${id}`)
                    .then(res => res.json())
                    .then(data => {
                        this.alertId = data.alertId;
                        this.alertStatus = data.auditorStatus;
                        this.alertReason = data.auditorReason ?? '-';
                        this.$wire.set('alertId', this.alertId);
                        this.$wire.set('alertStatus', this.alertStatus);
                        this.$wire.set('alertReason', this.alertReason);
                    })
                    .catch(() => {
                        this.alertReason = 'Failed to load';
                    })
                    .finally(() => {
                        this.loading = false;
                    });
            });
        },

        close() {
            this.alertId = null;
            this.alertReason = null;
            this.$wire.set('alertId', null);
            this.$wire.set('alertReason', null);
            this.open = false;
        },

        fixAlert() {
            const id = this.alertId;
            if (!id) return;
            this.$wire.fixAlert(id);
            this.close();
        }
    }
}
</script>
