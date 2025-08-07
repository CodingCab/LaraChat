<template>
    <container>
        <search-and-option-bar>
            <barcode-input-field :input_id="'barcode-input'" :url_param_name="'search'" @barcodeScanned="setCustomLabelText" :placeholder="$t('Enter custom label text')" ref="barcode"/>
            <template v-slot:buttons>
                <top-nav-button @click.native="printPDF" icon="print"/>
                <top-nav-button v-b-modal="'optionsModal'"/>
            </template>
        </search-and-option-bar>
        <div class="grid-col-12 pl-2 p-1">
            <div class="col-span-6 md:col-span-4 xl:col-span-6 mb-2 mb-sm-0">
                <breadcrumbs></breadcrumbs>
            </div>
            <div class="col-span-6 md:col-span-4 xl:col-span-4">
                <div class="col-span-8 d-flex justify-content-end justify-content-md-center justify-content-xl-end">
                    <header-upper class="small sd-none xs:sd-block">{{ $t('FROM') }}:</header-upper>
                    <input type="text" v-model="config.fromLetter" @keyup="changeNonSearchValue" @focus="$selectAllInputText" class="form-control mx-1 inline-input-sm px-1 text-center"/>
                    <input type="text" v-model.number="config.fromNumber" @keyup="changeNonSearchValue" @focus="$selectAllInputText" class="form-control mx-1 inline-input-sm px-1 text-center"/>
                    <header-upper class="small">TO</header-upper>
                    <input type="text" v-model="config.toLetter" @keyup="changeNonSearchValue" @focus="$selectAllInputText" class="form-control mx-1 inline-input-sm px-1 text-center"/>
                    <input type="text" v-model.number="config.toNumber" @keydown.enter="setFocusElementById('barcode-input')" @keyup="changeNonSearchValue" @focus="$selectAllInputText" class="form-control mx-1 inline-input-sm px-1 text-center"/>
                </div>
            </div>
            <div class="col-span-12 xs:col-span-12 md:col-span-4 xl:col-span-2 d-flex justify-content-end">
                <array-dropdown-select id="template_select_dropdown" class="ml-0 ml-sm-2" :items="templates" @item-selected="itemSelected" :itemSelected="config.templateSelected" :align-menu-right="true"/>
            </div>
        </div>
        <card class="mt-sm-2 bg-dark">
            <vue-pdf-embed :source="pdfUrl" :page="null"/>
            <div v-if="previewLimited" class="text-center text-white">{{ $t('Preview limited to 25 labels') }}</div>
        </card>

        <options-modal>
            <b-button variant="primary" block @click="downloadPDF" :disabled="downloadInProgress">
                {{ downloadInProgress ? $t('Please Wait...') : $t('Download PDF') }}
            </b-button>
        </options-modal>

    </container>
</template>

<script>

import url from "../../mixins/url.vue";
import helpers  from "../../helpers";
import helpersMixin from "../../mixins/helpers";
import VuePdfEmbed from 'vue-pdf-embed/dist/vue2-pdf-embed'
import api from "../../mixins/api";
import loadingOverlay from "../../mixins/loading-overlay";
import Breadcrumbs from "../Reports/Breadcrumbs.vue";
import OptionsModal from "../OptionsModal.vue";

export default {
    mixins: [loadingOverlay, url, helpersMixin, api],
    components: {
        OptionsModal,
        Breadcrumbs,
        VuePdfEmbed
    },
    data() {
        return {
            templates:[
                '50x20mm-1-per-page',
                '57x32mm-1-per-page',
                '100x50mm-1-per-page',
                '6x4in-1-per-page',
                '4x6in-2-per-page',
                '6x4in-3-per-page',
            ],
            config: {
                customLabelText: '',
                fromLetter: 'A',
                fromNumber: '1',
                toLetter: 'C',
                toNumber: '2',
                templateSelected: '6x4in-3-per-page',
            },
            viewDirectory: 'shelf-labels/',
            pdfUrl: '',
            previewLimited: false,
            downloadInProgress: false,
        }
    },

    mounted() {
        this.config.customLabelText = this.getUrlParameter('search', '');
        this.config.fromLetter = this.getUrlParameter('from-letter', 'A');
        this.config.fromNumber = this.getUrlParameter('from-number', 1);
        this.config.toLetter = this.getUrlParameter('to-letter', 'C');
        this.config.toNumber = this.getUrlParameter('to-number', 2);

        this.config.templateSelected = this.getUrlParameter('template-selected', '');

        // check if template selected is empty string
        // we use this approach in case url parameter is an empty string
        if(this.config.templateSelected === ''){
            this.config.templateSelected = helpers.getCookie('templateSelected', this.templates[0]);
        }

        this.loadPdfIntoIframe();
    },

    methods: {
        itemSelected(item) {
            this.config.templateSelected = item;
            helpers.setCookie('templateSelected', this.config.templateSelected);
            this.setFocusElementById('barcode-input');
            this.loadPdfIntoIframe();
        },

        setCustomLabelText(text) {
            this.config.customLabelText = text;
            this.loadPdfIntoIframe();
        },

        downloadPDF() {
            this.downloadInProgress = true;

            let data = {
                data: { labels: this.getLabelArray() },
                template: this.viewDirectory + this.config.templateSelected,
            };

            this.apiPostPdfDownload(data).then(response => {
                let url = window.URL.createObjectURL(new Blob([response.data]));
                let filename = this.config.templateSelected.replace('/', '_') + '.pdf';
                helpers.downloadFile(url, filename);
            }).catch(error => {
                this.displayApiCallError(error);
            }).finally(() => {
                this.downloadInProgress = false;
            });
        },

        printPDF() {
            if(this.currentUser().printer_id === null){
                this.notifyError(this.$t('Please select your printer on your profile page'));
                return;
            }

            this.showLoading();

            let data = {
                data: { labels: this.getLabelArray() },
                template: this.viewDirectory + this.config.templateSelected,
                printer_id: this.currentUser().printer_id,
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

        changeNonSearchValue() {
            this.config.customLabelText = '';
            this.loadPdfIntoIframe();
        },

        loadPdfIntoIframe() {
            this.previewLimited = false;

            // clone label array and limit label array to 25 labels
            let labels = _.cloneDeep(this.getLabelArray());
            if (labels.length > 25) {
                this.previewLimited = true;
                labels = labels.slice(0, 25);
            }

            console.log(this.config.templateSelected);

            let data = {
                data: { labels: labels },
                template: this.viewDirectory + this.config.templateSelected,
            };

            this.apiPostPdfPreview(data)
                .then(response => {
                    let blob = new Blob([response.data], { type: 'application/pdf' });
                    this.pdfUrl = URL.createObjectURL(blob);
                })
                .catch(error => {
                    this.displayApiCallError(error);
                })
                .finally(() => {
                    this.hideLoading();
                });
        },

        synchronizeUrlParameters() {
            // for some reason, when we already have the search param in place it
            // will not update it like the others, so we need to remove it first
            this.removeUrlParameter('search');
            const params = this.config.customLabelText
                ? {
                    'search': this.config.customLabelText,
                    'template-selected': this.config.templateSelected
                }
                : {
                    'from-letter': this.config.fromLetter,
                    'from-number': this.config.fromNumber,
                    'to-letter': this.config.toLetter,
                    'to-number': this.config.toNumber,
                    'template-selected': this.config.templateSelected
                };

            this.updateUrl(params);
        },

        getLabelArray() {
            if (!this.allNumbersAndLettersFilled) return [];
            if (this.config.customLabelText) return [this.config.customLabelText];

            let labels = [];
            let fromLetter = this.config.fromLetter.toUpperCase().charCodeAt(0);
            let toLetter = this.config.toLetter.toUpperCase().charCodeAt(0);

            for (let i = fromLetter; i <= toLetter; i++) {
                for (let j = i === fromLetter ? this.config.fromNumber : 1; j <= this.config.toNumber; j++) {
                    labels.push(String.fromCharCode(i) + j);
                }
            }

            return labels;
        },
    },
    computed: {
        allNumbersAndLettersFilled() {
            return this.config.fromLetter && this.config.fromNumber && this.config.toLetter && this.config.toNumber;
        }
    },
    watch: {
        config: {
            deep: true,
            handler() {
                this.synchronizeUrlParameters();
            }
        }
    }
}
</script>

<style scoped>
.inline-input-sm{
    max-width: 30px;
    height: 19px;
    padding: 0;
}
</style>
