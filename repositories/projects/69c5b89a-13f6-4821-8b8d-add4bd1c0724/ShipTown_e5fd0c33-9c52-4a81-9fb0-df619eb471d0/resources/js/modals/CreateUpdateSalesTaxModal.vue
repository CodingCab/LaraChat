<template>
    <b-modal body-class="ml-0 mr-0 pl-1 pr-1" :id="modalId" @hidden="emitNotification" size="xl" scrollable no-fade>
        <template #modal-header>
            <span>{{ modalTitle }}</span>
        </template>

        <div class="container">
            <input id="salesTaxCode" type="text" v-model="salesTax.code" class="form-control mb-2" :placeholder="$t('Sales Tax - Code')">
            <input id="salesTaxRate" type="number" v-model="salesTax.rate" class="form-control mb-2" :placeholder="$t('Sales Tax - Rate')"
                step="1">
        </div>
        <template #modal-footer>
            <b-button variant="secondary" class="float-right" @click="$bvModal.hide(modalId);">{{ $t('Cancel') }}</b-button>
            <b-button variant="primary" class="float-right" @click="saveSalesTax">{{ buttonText }}</b-button>
        </template>
    </b-modal>
</template>

<script>

import ProductCard from "../components/Products/ProductCard.vue";
import api from "../mixins/api.vue";
import Modals from "../plugins/Modals";

export default {
    components: { ProductCard },
    mixins: [api],

    beforeMount() {
        Modals.EventBus.$on('show::modal::' + this.modalId, (data) => {
            if (typeof data.id !== 'undefined' && data.id) {
                this.salesTax = {
                    id: data.id,
                    code: data.salesTax.code,
                    rate: data.salesTax.rate,
                };
                this.update = true;
            }

            this.$bvModal.show(this.modalId);
        })
    },

    data() {
        return {
            salesTax: {
                id: 0,
                code: '',
                rate: null,
            },
            saved: false,
            update: false,
            modalId: 'module-sales-taxes-create-update-sales-tax-modal',
        }
    },

    computed: {
        modalTitle() {
            return this.update ? this.$t('Update Sales Tax') : this.$t('Create Sales Tax');
        },
        buttonText() {
            return this.update ? this.$t('Update') : this.$t('Create');
        }
    },

    methods: {
        saveSalesTax() {
            if (this.update) {
                this.apiPutSalesTax(this.salesTax)
                    .then(response => {
                        if (response.status === 200) {
                            this.saved = true;
                            this.$bvModal.hide(this.modalId);
                            this.$snotify.success(this.$t('Sales tax updated successfully'));
                        }
                    })
                    .catch(error => {
                        this.displayApiCallError(error);
                    })
            } else {
                this.apiPostSalesTax(this.salesTax)
                    .then(response => {
                        if (response.status === 201) {
                            this.saved = true;
                            this.$bvModal.hide(this.modalId);
                            this.$snotify.success(this.$t('Sales tax created successfully'));
                        }
                    })
                    .catch(error => {
                        this.displayApiCallError(error);
                    })
            }
        },

        emitNotification() {
            Modals.EventBus.$emit('hide::modal::' + this.modalId, {
                salesTax: this.salesTax,
                saved: this.saved
            });

            this.saved = false;
            this.update = false;
            this.salesTax = {
                id: 0,
                code: '',
                rate: null,
            };
        }
    }
};

</script>
