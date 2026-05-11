<div x-data="reasonModal()" x-init="init()">

    <!-- MODAL -->
    <div
        class="fixed z-50 inset-0 overflow-y-auto ease-out duration-300"
        x-show="open"
        x-transition
        x-cloak
        style="display: none !important"
    >

        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">

            <!-- OVERLAY -->
            <div class="fixed inset-0 transition-opacity">
                <div class="absolute inset-0 bg-gray-100 dark:bg-gray-900 opacity-50"></div>
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>

            <!-- MODAL CONTENT -->
            <div
                class="px-4 py-6 inline-block align-bottom min-h-[550px] overflow-y-auto rounded-sm bg-white dark:bg-slate-700 text-left shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full"
                role="dialog"
            >
                <div wire:loading class="flex w-full justify-center text-center bg-red-300 py-1 animate-pulse text-xs px-4 text-white mb-12" >loading. . .</div>


                <!-- HEADER -->
                <div class="flex items-center justify-between mb-6 pb-4 border-b border-stone-200 dark:border-slate-600">
                    <div class="w-full">
                        <h2 class="text-xl font-bold text-stone-900 dark:text-slate-100">
                            <span x-text="alertId"></span>
                        </h2>
                        <span class="inline-flex items-center justify-center text-center mt-2 appearance-none rounded-sm text-xs font-semibold uppercase tracking-wider bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-700 px-3 py-1.5" x-text="alertStatus"></span>
                    </div>
                    <button @click="close()" class="text-stone-400 hover:text-stone-600 dark:text-slate-400 dark:hover:text-slate-200 cursor-pointer transition-none">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>


                <!-- BODY -->
                <div class="flex flex-col mt-4 prose prose-sm break-words dark:text-slate-400">

                    <!-- LOADING -->
                    <template x-if="loading">
                        <div class="animate-pulse text-gray-400">
                            Memuat data...
                        </div>
                    </template>

                    <!-- CONTENT -->
                    <template x-if="!loading">
                        <div class="bg-stone-50 dark:bg-slate-800 rounded-sm p-4 border border-stone-200 dark:border-slate-600">
                            <span class="text-label text-stone-500 dark:text-slate-400 mb-2 block">Reason</span>
                            <p class="text-sm text-left dark:text-slate-300" x-html="alertReason"></p>
                        </div>
                    </template>

                </div>


                <!-- FOOTER -->
                <div class="px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse mt-6 mb-6 bottom-0 right-0 absolute">

                    <span class="flex w-full rounded-md shadow-sm sm:ml-3 sm:w-auto">
                        <button
                        @click="fixAlert()"
                        wire:loading.attr="disabled"
                        :disabled="loading"
                        class="cursor-pointer inline-flex items-center gap-2 rounded-sm px-4 py-2 bg-stone-900 dark:bg-slate-200 text-white dark:text-stone-900 text-sm font-semibold disabled:opacity-50 transition-none"
                    >


                        <!-- Text normal -->
                        <span wire:loading.remove wire:target="auditing">
                            Fix Alert
                        </span>

                        <!-- Text loading -->
                        <span wire:loading wire:target="auditing">
                            Saving...
                        </span>

                    </button>

                    </span>

                    <span class="mt-3 flex w-full rounded-md shadow-sm sm:mt-0 sm:w-auto">
                        <button @click="close()" class="cursor-pointer inline-flex justify-center w-full rounded-sm border border-stone-300 dark:border-slate-600 px-4 py-2 bg-white dark:bg-slate-800 text-base font-medium text-stone-700 dark:text-slate-200 shadow-sm transition-none sm:text-sm"  >
                            Close
                        </button>
                    </span>

                </div>

            </div>
        </div>
    </div>

    <script>

function reasonModal()
{
    return {

        open: false,
        loading: false,

        alertId: '',
        alertStatus: '',
        alertReason: '',


        init()
        {
            window.addEventListener('open-reason-modal', (e) => {

                const id = e.detail.id;

                this.open = true;



                this.loading = true;

                fetch(`/rest/fix/${id}`)
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

        clear(){
            this.alertId = null;
            this.alertReason = null;
            // sync ke Livewire
            this.$wire.set('alertId', null);
            this.$wire.set('alertReason', null);
        },

        fill(data)
        {
            this.alertId = data.alertId;
            this.alertStatus = data.auditorStatus;
            this.alertReason = data.auditorReason ?? '-';
            // sync ke Livewire
            this.$wire.set('alertId', this.alertId);
            this.$wire.set('alertStatus', this.alertStatus);
            this.$wire.set('alertReason', this.alertReason);
        },

        close()
        {
            this.clear();
            this.open = false;
        },

        fixAlert(data)
        {

            // gunakan alertId dari state Alpine
            const id = this.alertId;

            if (!id) return;
            this.$wire.fixAlert(id);


            this.close();
        }

    }
}

</script>
</div>