<template>
    <options-modal :title="$t('Import Products')" :showStocktakeInput="false" body-class="ml-0 mr-0 pl-1 pr-1" :id="modalId" @shown="resetForm" @hidden="emitNotification" size="md" scrollable no-fade>
        <template #menu-buttons>
            <b-button variant="link" class="small mb-1 text-nowrap" href="/templates/product_import_template.csv" target="_blank">
                <!-- only on large screens -->
                <span class="d-none d-lg-inline small">{{ $t('Download Template') }}</span>
                <!-- only on small screens -->
                <span class="d-inline d-lg-none small">{{ $t('Template') }}</span>
            </b-button>
        </template>

        <csv-import v-model="csv"
                    headers
                    canIgnore
                    autoMatchFields
                    autoMatchIgnoreCase
                    :loadBtnText="$t('Load')"
                    :ignoreOptionText="$t('')"
                    ref="csvImport"
                    @fileUpload="importProducts"
                    @input="getFileName"
                    :map-fields="mapFields">
            <template #thead>
                <tr>
                    <th>{{ $t('My Fields') }}</th>
                    <th>{{ $t('Column') }}</th>
                </tr>
            </template>
        </csv-import>
    </options-modal>
</template>

<script>
import api from "../mixins/api.vue";
import Modals from "../plugins/Modals";
import OptionsModal from "../components/OptionsModal.vue";

export default {
    components: {OptionsModal},
    mixins: [api],

    beforeMount() {
        Modals.EventBus.$on('show::modal::' + this.modalId, () => {
            this.$bvModal.show(this.modalId);
        })
    },

    data() {
        return {
            modalId: 'import-products-modal',
            refreshList: false,
            csv: null,
            file: null,
            mapFields: null,
            sending: false,
            mappedFields: null,
        }
    },

    mounted() {
        this.getHeaders();
    },

    methods: {
        resetForm() {
            this.csv = null;
            this.file = null;
            this.mappedFields = null;
            this.sending = false;
            this.mappedFields = null;
            this.getHeaders();
        },

        getFileName(e) {
            this.mappedFields = Object.fromEntries(
                Object.entries(e.map).filter(([_, value]) => value !== null)
            );
            if (e.file && e.file.files.length > 0) {
                this.file = e.file.files[0];
            } else {
                this.file = null;
            }
        },
        getHeaders() {
            const params = {
                'per_page': 0
            }

            this.apiGetCsvImportProducts(params)
                .then(({data}) => {
                    if (typeof data.meta !== 'undefined' && typeof data.meta.columns !== 'undefined') {
                        this.mapFields = data.meta.columns
                            .map(item => {
                                const name = item.expression.split('.');
                                return name[name.length - 1];
                            })
                            .filter(fieldName => {
                                return !['id', 'created_at', 'processed_at', 'updated_at'].includes(fieldName.toLowerCase());
                            });
                    }
                })
                .catch((error) => {
                    this.displayApiCallError(error);
                });
        },

        importProducts() {
            this.sending = true;

            const formData = new FormData();
            formData.append('file', this.file);
            formData.append('mappedFields', JSON.stringify(this.mappedFields));

            this.apiPostCsvImportProducts(formData)
                .then(response => {
                    if (
                        typeof response.data !== 'undefined' &&
                        typeof response.data.data !== 'undefined' &&
                        typeof response.data.data.success !== 'undefined' &&
                        response.data.data.success
                    ) {
                        this.notifySuccess(this.$t('Products imported successfully'));
                        this.refreshList = true;
                        this.csv = null;
                        this.file = null;
                        this.mappedFields = null;
                        this.$bvModal.hide(this.modalId);
                    }
                })
                .catch(error => {
                    this.displayApiCallError(error);
                })
                .finally(() => {
                    this.sending = false;
                });
        },

        emitNotification() {
            Modals.EventBus.$emit('hide::modal::' + this.modalId, {refreshList: this.refreshList});
            this.csv = null;
            this.refreshList = false;
            this.mapFields = null;
            this.sending = false;
            this.file = null;
            this.mappedFields = null;
        },
    },

    computed: {
        fieldsMapped() {
            return this.mappedFields && Object.keys(this.mappedFields).length > 0;
        }
    },
};

</script>
