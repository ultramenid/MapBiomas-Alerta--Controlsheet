// Alpine data for the alert reason / audit modals (partials/auditorReason and
// partials/auditing). These used to be plain functions declared in inline
// <script> tags inside those Livewire component views — but wire:navigate
// swaps pages in without running such scripts first, so x-data="reasonModal()"
// threw "reasonModal is not defined", the modal got an empty data object, and
// open/close fell through to window.open/window.close ("Illegal invocation").
// Registered here, the providers exist before Alpine walks any tree and
// survive every navigate/morph. Open events arrive via @open-…-modal.window
// directives on the roots, so Alpine removes the listeners when a tree is
// destroyed (the old init() leaked one window listener per re-init).

document.addEventListener('alpine:init', () => {
    Alpine.data('reasonModal', () => ({
        open: false,
        loading: false,
        alertId: '',
        alertStatus: '',
        alertReason: '',

        openReason(id) {
            this.open = true;
            this.loading = true;

            fetch(`/rest/fix/${id}`)
                .then((res) => res.json())
                .then((data) => {
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
        },
    }));

    Alpine.data('auditModal', () => ({
        open: false,
        loading: false,
        alertId: '',

        openAudit(id) {
            this.open = true;
            this.loading = true;

            const endpoint = this.$root.dataset.auditEndpoint || '/rest/audit';
            fetch(`${endpoint}/${id}`)
                .then((res) => res.json())
                .then((data) => this.fill(data))
                .catch(() => {
                    this.alertReason = 'Failed to load';
                })
                .finally(() => {
                    this.loading = false;
                });
        },

        fill(data) {
            // The reason editor and the status select both sit under wire:ignore, so
            // Livewire never morphs them — without pushing the fetched values into the
            // DOM here they keep showing the previously opened alert, and whatever is
            // on screen is what gets saved.
            const reason = data.auditorReason ?? '';
            const status = data.auditorStatus ?? 'pre-approved';

            const textarea = document.getElementById('alertReason');
            if (textarea) textarea.dataset.content = reason; // read by editor.on('init')
            window.tinymce?.get('alertReason')?.setContent(reason);

            const select = document.getElementById('alertStatusSelect');
            if (select) select.value = status;

            this.alertId = data.alertId;
            this.$wire.set('alertId', data.alertId);
            this.$wire.set('alertStatus', status);
            this.$wire.set('statusAlert', data.alertStatus);
            this.$wire.set('alertReason', reason);
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
            // Pull the editor content once, here, instead of a Livewire round-trip on
            // every change/blur.
            const editor = window.tinymce?.get('alertReason');
            if (editor) this.$wire.set('alertReason', editor.getContent(), false);
            this.$wire.auditing(id);
        },
    }));
});
