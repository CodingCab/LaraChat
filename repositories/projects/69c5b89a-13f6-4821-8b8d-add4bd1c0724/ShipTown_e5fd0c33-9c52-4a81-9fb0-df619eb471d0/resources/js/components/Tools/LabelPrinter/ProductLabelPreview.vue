<template>
    <container>
        <search-and-option-bar>
            <array-dropdown-select class="pt-2 ml-0 ml-sm-2" :items="templates" @item-selected="changeLabel" :item-selected.sync="templateSelected" :align-menu-right="true" />
            <template v-slot:buttons>
                <!--                <top-nav-button v-b-modal="'quick-actions-modal'"/>-->
                <array-dropdown-select class="pt-2 mr-2" :items="printers" placeholder="Select printer" object-key="name" @item-selected="changePrinter" :item-selected.sync="printerSelected" :align-menu-right="true" />
                <input type="number" dusk="product-label-count-input" class="form-control text-center align-content-center" v-model="labelCount" @focus="simulateSelectAll" style="width: 80px;">
                <top-nav-button @click.native="printPDF" icon="print" />
            </template>
        </search-and-option-bar>

        <card class="bg-dark">
            <vue-pdf-embed ref="pdfRef" :source="pdfUrl" :page="null" />
            <div v-if="previewLimited" class="text-center text-white">{{ $tc('Preview limited to {n} labels', 25) }}</div>
        </card>

        <b-modal id="quick-actions-modal" no-fade hide-header @hidden="setFocusElementById('barcode-input')">
            <stocktake-input v-bind:auto-focus-after="100"></stocktake-input>

            <hr>
            <b-button variant="primary" block @click="downloadPDF" :disabled="downloadInProgress">
                {{ downloadInProgress ? $t('Please Wait...') : $t('Download PDF') }}
            </b-button>
            <template #modal-footer>
                <b-button variant="secondary" class="float-right" @click="$bvModal.hide('quick-actions-modal');">
                    {{ $t('Cancel') }}
                </b-button>
                <b-button variant="primary" class="float-right" @click="$bvModal.hide('quick-actions-modal');">
                    {{ $t('OK') }}
                </b-button>
            </template>
        </b-modal>

    </container>
</template>

<script>

import url from "../../../mixins/url.vue";
import helpers from "../../../helpers";
import helpersMixin from "../../../mixins/helpers";
import VuePdfEmbed from 'vue-pdf-embed/dist/vue2-pdf-embed'
import api from "../../../mixins/api";
import loadingOverlay from "../../../mixins/loading-overlay";
import Vue from "vue";

export default {
    mixins: [loadingOverlay, url, helpersMixin, api],

    components: { VuePdfEmbed },

    props: {
        product: Object,
    },

    data() {
        return {
            viewDirectory: 'product-labels/',
            labelCount: 1,
            templates: [],
            templateSelected: '',
            printers: [],
            printersName: [],
            printerSelected: '',
            pdfUrl: '',
            previewLimited: false,
            downloadInProgress: false,
        }
    },

    mounted() {
        this.templates = [
            '57x32mm_Price_Tag',
            '57x32mm_Barcode_Label',
            '4x6in_Price_Tag',
            '4x6in_Barcode_Label',
            '20x80mm_Price_Tag',
            '20x80mm_Barcode_Tag',
            '20x32mm_Price_Tag',
            '20x32mm_Barcode_Tag',
            '30x40mm_Price_Tag',
            '30x40mm_Barcode_Tag',
        ];

        this.templateSelected = this.getUrlParameter('template-selected', helpers.getCookie('productLabelsLastTemplateSelected', this.templates[0]));

        this.apiGetPrintNodePrinters()
            .then(({ data }) => {
                this.printers = data.data.map(printer => {
                    let name = printer.name;
                    if (printer.computer && printer.computer.name) {
                        name += ' - ' + printer.computer.name;
                    }
                    return {
                        id: printer.id,
                        name: name,
                    }
                });

                if (this.currentUser().printers.product_labels) {
                    const selected = this.printers.find(printer => printer.id === this.currentUser().printers.product_labels);
                    if (typeof selected !== 'undefined') {
                        this.printerSelected = selected.name;
                    }
                }
            })
            .catch(e => {
                this.displayApiCallError(e);
            });
    },

    methods: {
        changeLabel(template) {
            this.templateSelected = template;
        },

        changePrinter(printer) {
            Vue.prototype.$currentUser.printers = Vue.prototype.$currentUser.printers || {};
            Vue.prototype.$currentUser.printers.product_labels = printer.id;
            this.printerSelected = printer.name;

            this.apiPostUserMe({
                'printers': {
                    'product_labels': printer.id,
                }
            })
            .then(({ data }) => {
                Vue.prototype.$currentUser.printers.product_labels = data.data.printers.product_labels;
            })
            .catch(e => {
                this.displayApiCallError(e);
            });
        },

        downloadPDF() {
            this.downloadInProgress = true;

            let product_sku = [];

            for (let i = 0; i < this.labelCount; i++) {
                product_sku.push(this.product['sku']);
            }

            let data = {
                data: { product_sku },
                template: this.viewDirectory + this.templateSelected,
            };

            this.apiPostPdfDownload(data)
                .then(response => {
                    let url = window.URL.createObjectURL(new Blob([response.data]));
                    let filename = this.templateSelected.replace('/', '_') + '.pdf';
                    helpers.downloadFile(url, filename);
                }).catch(error => {
                    this.displayApiCallError(error);
                }).finally(() => {
                    this.downloadInProgress = false;
                });
        },

        printPDF() {
            if (typeof this.currentUser().printers.product_labels === 'undefined' || this.currentUser().printers.product_labels === null) {
                this.notifyError(this.$t('Please select your printer first'));
                return;
            }

            let product_sku = [];

            for (let i = 0; i < this.labelCount; i++) {
                product_sku.push(this.product['sku']);
            }

            let data = {
                data: { product_sku },
                template: this.viewDirectory + this.templateSelected,
                printer_id: this.currentUser().printers.product_labels,
            };

            this.apiPostPdfPrint(data).then(() => {
                this.notifySuccess(this.$t('PDF sent to printer'));
            }).catch(error => {
                this.displayApiCallError(error);
            }).finally(() => {
                this.hideLoading();
                this.setFocusElementById('barcode-input')
            });
        },

        loadPdfIntoIframe() {
            this.showLoading();
            this.previewLimited = false;

            let product_sku = [];

            for (let i = 0; i < this.labelCount; i++) {
                product_sku.push(this.product['sku']);
            }

            let previewSku = product_sku;
            if (previewSku.length > 25) {
                this.previewLimited = true;
                previewSku = previewSku.slice(0, 25);
            }

            let data = {
                data: { product_sku: previewSku },
                template: this.viewDirectory + this.templateSelected,
            };

            this.apiPostPdfPreview(data)
                .then(response => {
                    let blob = new Blob([response.data], { type: 'application/pdf' });
                    this.pdfUrl = URL.createObjectURL(blob);
                }).catch(error => {
                    this.displayApiCallError(error);
                }).finally(() => {
                    this.hideLoading();
                });
        },
    },

    watch: {
        templateSelected() {
            helpers.setCookie('productLabelsLastTemplateSelected', this.templateSelected);
            this.loadPdfIntoIframe();
        },

        labelCount() {
            this.loadPdfIntoIframe();
        },
    },
}
</script>
