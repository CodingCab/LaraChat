<template>
    <b-modal body-class="ml-0 mr-0 p-0" :id="modalId" size="md" scrollable no-fade hide-header hide-footer>
        <div class="card card-default mb-0">
            <div class="card-header">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span>{{ $t('Transaction Status') }}</span>
                </div>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    {{ ('Status') }}: <strong>{{ displayStatus }}</strong>
                </div>
                <template>
                    <hr>
                    <div class="mt-4">
                        <div class="d-flex justify-content-between">
                            {{ $t('Change') }}: <span>{{ displayChange }}</span>
                        </div>
                    </div>
                </template>
            </div>
            <div class="card-footer d-flex justify-content-end">
                <b-button id="data-collection-transaction-status-modal-close-button" variant="secondary" class="mr-2" @click="closeModal" data-close>Close</b-button>
            </div>
        </div>
    </b-modal>
</template>

<script>

import api from "../mixins/api.vue";
import Modals from "../plugins/Modals";

export default {
    mixins: [api],

    props: {
        details: {
            type: Object,
            required: true
        },
        printer: {
            type: Object | null,
            required: true
        }
    },

    beforeMount() {
        Modals.EventBus.$on(`show::modal::${this.modalId}`, () => {
            this.$bvModal.show(this.modalId);
            if (this.printer) {
                this.printReceipt();
            } else {
                this.status = 'posted';
            }
        })
    },

    data() {
        return {
            modalId: 'data-collection-transaction-status-modal',
            status: 'posting',
        }
    },

    computed: {
        displayChange() {
            return Math.abs(Number(this.details.total_outstanding)).toFixed(2);
        },
        displayStatus() {
            if (this.status === 'posting') {
                return 'Posting transaction';
            } else if (this.status === 'printing') {
                return 'Printing receipt';
            } else if (this.status === 'printed') {
                return 'Receipt printed';
            } else {
                return 'Transaction posted';
            }
        }
    },

    methods: {
        printReceipt() {
            this.status = 'printing';

            const data = {
                id: this.details.id,
                printer_id: this.printer.id,
            };

            this.apiPrintTransactionReceipt(data)
                .then(() => {
                    this.status = 'printed';
                })
                .catch(e => {
                    this.displayApiCallError(e);
                });
        },

        closeModal() {
            this.$bvModal.hide(this.modalId);

            Modals.EventBus.$emit(`hide::modal::${this.modalId}`, {
                archiveTransaction: true,
            });
        }
    }
};
</script>
