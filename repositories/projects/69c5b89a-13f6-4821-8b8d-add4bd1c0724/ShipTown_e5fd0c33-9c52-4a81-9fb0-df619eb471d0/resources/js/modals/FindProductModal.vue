<template>
    <b-modal body-class="ml-0 mr-0 pl-1 pr-1" id="find-product-modal" size="xl" scrollable no-fade hide-header @shown="findProductsContainingSearchText()">
        <search-and-option-bar >
            <barcode-input-field
                :input_id="'product_search_input'"
                :placeholder="$t('Search')"
                ref="barcode"
                :showKeyboardOnFocus="true"
                @barcodeScanned="findText"
            />
        </search-and-option-bar>
        <div class="table-responsive py-2">
        <table class="table-responsive table-sm table-hover">
            <tr v-for="product in products" @click="selectProduct(product)">
                <td class="table-hover">
                    {{ product['sku'] }} - {{ product['name'] }}
                </td>
                <td>
                    <button @click="selectProduct(product)">{{ $t('select') }}</button>
                </td>
            </tr>
        </table>
        </div>
        <template #modal-footer>
            <b-button variant="secondary" class="float-right" @click="$bvModal.hide('find-product-modal');">
                {{ $t('Cancel') }}
            </b-button>
            <b-button variant="primary" class="float-right" @click="$bvModal.hide('find-product-modal');">
                {{ $t('OK') }}
            </b-button>
        </template>
    </b-modal>
</template>

<script>

import ProductCard from "../components/Products/ProductCard.vue";
import api from "../mixins/api.vue";
import Modals from "../plugins/Modals";
import url from "../mixins/url.vue";

export default {
    components: {ProductCard},
    mixins: [api, url],
    props: {

    },
    beforeMount() {
        this.products = [];

        Modals.EventBus.$on('show::modal::find-product-modal', (data) => {
            this.callback = data.callback;
            this.$bvModal.show('find-product-modal');
        })
    },

    data() {
        return {
            callback: null,
            products: [],
        }
    },

    methods: {
        selectProduct(product) {
            this.$bvModal.hide('find-product-modal');
            this.$nextTick(() => {
                this.callback(product);
            });
        },

        findText(searchText) {
            this.searchText = searchText;
            this.findProductsContainingSearchText();
        },

        findProductsContainingSearchText: function(page = 1) {
            // this.showLoading();

            const params = {};
            params['filter[search]'] = this.searchText;
            params['include'] = 'inventory,tags,prices,aliases,inventory.warehouse,inventoryMovementsStatistics,inventoryTotals,saleTax';
            params['sort'] = this.getUrlParameter('sort', '-quantity');

            this.apiGetProducts(params)
                .then(({data}) => {
                    this.products = data.data;
                    // this.reachedEnd = data.data.length === 0;
                    // this.pagesLoadedCount = page;
                    //
                    // this.scroll_percentage = (1 - this.per_page  / this.products.length) * 100;
                    // this.scroll_percentage = Math.max(this.scroll_percentage, 70);
                    //
                    //
                    // // Remove duplicate product if there are two products with the same sku (possible because of exact search)
                    // if (this.products.length > 1  && this.products[0].sku === this.products[1].sku) {
                    //     this.products.shift();
                    // }
                })
                .catch((error) => {
                    this.displayApiCallError(error);
                })
                .finally(() => {
                    // this.hideLoading();
                });
            return this;
        }
    }
};

</script>
