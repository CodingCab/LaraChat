<template>
    <b-modal body-class="ml-0 mr-0 p-0" :id="modalId" size="lg" @hidden="emitNotification" scrollable no-fade
             hide-header hide-footer>
        <div class="card card-default mb-0">
            <div class="card-header">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span>{{ $t('Printer Selection') }}</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-2" v-for="printer in printers" :key="printer['id']"
                     :class="{'table-primary': isSelectedPrinter(printer['id'])}">
                    <div class="col-lg-6 col-md-12">
                        {{ printer['name'] }} <br>
                        <span class="small text-secondary">
                            {{ printer['computer']['name'] }}<br>
                            <status-icon :status="printer['state'] === 'online'" class="small"/> {{ printer['id'] }}
                        </span>
                    </div>
                    <div class="col text-right">
                        <button type="button" @click.prevent="printTestPage(printer)"
                                class="btn btn-primary btn-sm mb-1">
                            {{ $t('PDF Test') }}
                        </button>
                        <button type="button" @click.prevent="printEplTestPage(printer)"
                                class="btn btn-primary btn-sm mb-1">{{ $t('EPL Test') }}
                        </button>
                        <button type="button" @click.prevent="printRawTestPage(printer)"
                                class="btn btn-primary btn-sm mb-1">{{ $t('Raw Text Test') }}
                        </button>
                        <button type="button" @click.prevent="setChosenPrinter(printer)"
                                v-if="!isSelectedPrinter(printer['id'])" class="btn btn-primary btn-sm mb-1">{{ $t('Use') }}
                        </button>
                    </div>
                </div>

                <hr class="mt4">

                <div class="row mt-4 d-flex justify-content-end">
                    <b-button variant="primary" @click="closeModal">{{ $t('Close') }}</b-button>
                </div>
            </div>
        </div>
    </b-modal>
</template>

<script>

import api from "../mixins/api.vue";
import Modals from "../plugins/Modals";
import StatusIcon from "../components/Settings/Automations/StatusIcon.vue";

export default {
    components: {
        'status-icon': StatusIcon
    },
    mixins: [api],

    beforeMount() {
        this.product = null;

        Modals.EventBus.$on(`show::modal::${this.modalId}`, (data) => {
            this.loadPrinters();
            this.selectedPrinter = data.printer;
            this.openTransactionStatusModal = data.openTransactionStatusModal;
            this.$bvModal.show(this.modalId);
        })
    },

    data() {
        return {
            modalId: 'set-transaction-printer-modal',
            selectedPrinter: null,
            openTransactionStatusModal: false,
            printers: [],
        }
    },

    methods: {
        loadPrinters() {
            this.apiGetPrintNodePrinters()
                .then(({data}) => {
                    this.printers = data.data;
                })
                .catch(e => {
                    this.displayApiCallError(e);
                });
        },

        printTestPage(printer) {
            let data = {
                'printer_id': printer.id,
                'pdf_url': 'https://api.printnode.com/static/test/pdf/label_4in_x_6in.pdf'
            };

            this.apiPostPrintNodePrintJob(data)
                .then(() => {
                    this.$snotify.info(this.$t('Test page sent to PrintNode'));
                })
                .catch(e => {
                    this.displayApiCallError(e);
                });
        },

        printEplTestPage(printer) {
            let data = {
                'printer_id': printer.id,
                'content_type': 'raw_base64',
                'base64raw_url': 'http://management.products.api.test/label_4in_x_6in.epl'
            };

            this.apiPostPrintNodePrintJob(data)
                .then(() => {
                    this.$snotify.info(this.$t('Test page sent to PrintNode'));
                })
                .catch(e => {
                    this.displayApiCallError(e);
                });
        },

        printRawTestPage(printer) {
            let data = {
                'printer_id': printer.id,
                'content_type': 'raw_base64',
                'content': btoa('Hello World/n' +
                    'blue rectangle/n' +
                    'A50,50'
                ),
            };

            this.apiPostPrintNodePrintJob(data)
                .then(() => {
                    this.$snotify.info('Test page sent to PrintNode');
                })
                .catch(e => {
                    this.displayApiCallError(e);
                });
        },

        setChosenPrinter(printer) {
            this.selectedPrinter = printer;

            window.localStorage.setItem('selectedTransactionsPrinter', JSON.stringify(printer));
        },

        isSelectedPrinter: function (printerId) {
            return this.selectedPrinter && this.selectedPrinter.id && printerId === this.selectedPrinter.id;
        },

        emitNotification() {
            Modals.EventBus.$emit(`hide::modal::${this.modalId}`, {
                printer: this.selectedPrinter,
                openTransactionStatusModal: this.openTransactionStatusModal
            });
        },

        closeModal() {
            this.$bvModal.hide(this.modalId);
        }
    }
};
</script>
