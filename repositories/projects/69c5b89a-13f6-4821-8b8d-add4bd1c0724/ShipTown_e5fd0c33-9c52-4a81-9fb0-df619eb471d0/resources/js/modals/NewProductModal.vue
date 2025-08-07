<template>
    <b-modal body-class="ml-0 mr-0 pl-1 pr-1" :id="modalId" @hidden="emitNotification" size="md" scrollable no-fade>
        <template #modal-header>
            <span>{{ modalTitle }}</span>
        </template>

        <div class="container">
            <input id="newProductSku" type="text" :disabled="!isCreatingProduct" v-model="newProduct.sku" class="form-control mb-2"
                :placeholder="$t('Product SKU')">
            <input id="newProductName" type="text" v-model="newProduct.name" class="form-control mb-2" :placeholder="$t('Product Name')">
            <input id="newProductNumber" type="text" v-model="newProduct.product_number" class="form-control mb-2" :placeholder="$t('Product number')">
            <input id="newProductPrice" type="number" :disabled="!isCreatingProduct" v-model="newProduct.price" class="form-control mb-2"
                :placeholder="$t('Product Price')">
            <select class="form-control" v-model="newProduct.default_tax_code">
                <option value="" disabled>{{ $t('Sale Tax') }}</option>
                <option v-for="saleTax in saleTaxes" :key="saleTax.id" :value="saleTax.code">{{ saleTax.rate }}</option>
            </select>
        </div>
        <template #modal-footer>
            <div class="d-flex justify-content-between w-100 m-0">
                <div>
                    <b-button variant="primary" class="float-right" @click="openImportProductsModal">
                        {{ $t('Import CSV File') }}
                    </b-button>
                </div>
                <div class="d-flex">
                    <b-button variant="secondary" class="float-right mr-2" @click="$bvModal.hide(modalId);">
                        {{ $t('Cancel') }}
                    </b-button>
                    <b-button id="ok_button" variant="primary" class="float-right" @click="createNewProduct">
                        {{ saveButtonText }}
                    </b-button>
                </div>
            </div>
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
            this.getSaleTaxes();

            this.product = data['product'];

            this.newProduct = {
                sku: '',
                name: '',
                product_number: '',
                price: '0.00',
                default_tax_code: 'VAT_0',
                type: 'simple',
            };

            if (this.product) {
                this.newProduct.sku = this.product.sku;
                this.newProduct.name = this.product.name;
                this.newProduct.product_number = this.product.product_number;
                this.newProduct.price = this.product.price;
                this.newProduct.default_tax_code = this.product.default_tax_code || 'VAT_0';
                this.newProduct.type = 'simple';

                this.updateProduct = true;
            }

            this.$bvModal.show(this.modalId);
        })
    },

    data() {
        return {
            newProduct: {
                sku: '',
                name: '',
                product_number: '',
                price: '',
                default_tax_code: '',
                type: 'simple',
            },
            modalId: 'new-product-modal',
            product: undefined,
            saleTaxes: null,
            updateProduct: false,
        }
    },

    computed: {
        isCreatingProduct() {
            return this.product === null || (this.product === undefined);
        },
        modalTitle() {
            return this.updateProduct ? this.$t('Update Product') : this.$t('New Product');
        },
        saveButtonText() {
            return this.updateProduct ? this.$t('Update') : this.$t('Create');
        }
    },

    methods: {
        createNewProduct() {
            this.apiPostProducts(this.newProduct)
                .then(() => {
                    this.$bvModal.hide(this.modalId);
                })
                .catch(error => {
                    this.displayApiCallError(error);
                })
        },

        openImportProductsModal() {
            this.$bvModal.hide(this.modalId);
            this.$modal.showImportProductsModal();
        },

        emitNotification() {
            Modals.EventBus.$emit('hide::modal::' + this.modalId, this.newProduct);
        },

        getSaleTaxes() {
            this.apiGetSalesTaxes()
                .then(({ data }) => {
                    this.saleTaxes = data.data
                })
                .catch((error) => {
                    this.displayApiCallError(error);
                });
        }
    }
};

</script>
