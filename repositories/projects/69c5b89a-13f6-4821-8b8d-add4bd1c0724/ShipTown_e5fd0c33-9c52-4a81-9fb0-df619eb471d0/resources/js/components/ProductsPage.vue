<template>
    <div>
        <search-and-option-bar-observer />
        <search-and-option-bar :isStickable="true">
            <barcode-input-field :input_id="'barcode_input'" :placeholder="$t('Search')" ref="barcode" :url_param_name="'search'" @refreshRequest="reloadProductList" />
            <template v-slot:buttons>
                <button id="plus_button" @click="showNewProductModal" type="button" class="btn btn-primary ml-2">
                    <font-awesome-icon icon="plus" class="fa-lg"></font-awesome-icon>
                </button>
                <top-nav-button v-b-modal="'optionsModal'"/>
            </template>
        </search-and-option-bar>

        <div class="row pl-2 p-0">
            <breadcrumbs></breadcrumbs>
        </div>

        <template v-if="isLoading === false && products !== null && products.length === 0">
            <div class="text-secondary text-center mt-3">
                {{ $t('Click + to create your first product one') }}<br>
            </div>
        </template>

        <template v-if="products">
            <div class="row p-1" v-for="product in products" :key="product.id">
                <div class="col">
                    <product-card :product="product" :expanded="products.length === 1" />
                </div>
            </div>
        </template>

        <div class="row">
            <div class="col">
                <div ref="loadingContainerOverride" style="height: 32px"></div>
            </div>
        </div>

        <options-modal>
<!--            Content here -->
        </options-modal>
        <import-products-modal></import-products-modal>
        <assemble-product-quantity-modal></assemble-product-quantity-modal>
    </div>
</template>

<script>
import loadingOverlay from '../mixins/loading-overlay';
import ProductCard from "./Products/ProductCard";
import BarcodeInputField from "./SharedComponents/BarcodeInputField";
import url from "../mixins/url";
import api from "../mixins/api";
import helpers from "../mixins/helpers";
import Modals from "../plugins/Modals";
import Breadcrumbs from "./Reports/Breadcrumbs.vue";
import OptionsModal from "./OptionsModal.vue";

export default {
    mixins: [loadingOverlay, url, api, helpers],

    components: {
        OptionsModal,
        Breadcrumbs,
        ProductCard,
        BarcodeInputField
    },

    data: function () {
        return {
            pagesLoadedCount: 1,
            reachedEnd: false,
            products: null,
            per_page: 20,
            scroll_percentage: 70,
        };
    },

    watch: {
        '$route' (to, from) {
            if (to.path === from.path) {
                this.$nextTick(() => {
                    this.reloadProductList();
                });
            }
        }
    },

    mounted() {
        window.onscroll = () => this.loadMore();

        Modals.EventBus.$on('hide::modal::new-product-modal', (newProduct) => {
            if (typeof newProduct !== 'undefined' && typeof newProduct.sku !== 'undefined' && newProduct.sku) {
                this.setUrlParameter('search', newProduct.sku);
                this.reloadProductList();
                this.setFocusElementById('barcode_input');
            }
        });

        Modals.EventBus.$on('hide::modal::import-products-modal', (data) => {
            if (typeof data.refreshList !== 'undefined' && data.refreshList) {
                this.reloadProductList();
            }
        });

        Modals.EventBus.$on('hide::modal::assemble-product-quantity-modal', (data) => {
            if (data && data.saveChanges) {
                this.assembleProduct(data.quantity, data.productId, data.disassemble);
            } else if (data && !data.saveChanges) {
                this.$snotify.info(this.$t('Assembly cancelled'));
            } else if (typeof data === 'undefined') {
                this.$snotify.error(this.$t('No data provided'));
            }
        });

        this.reloadProductList();
    },

    methods: {
        onScan(decodedText, decodedResult) {
            // handle the message here :)
            this.notifySuccess(decodedText);
            this.notifySuccess(decodedResult);
        },

        findText(search) {
            this.setUrlParameter('search', search);
            this.reloadProductList();
        },

        reloadProductList() {
            this.products = null;

            if (this.getUrlParameter('search')) {
                this.findProductsWithExactSku();
            }

            this.findProductsContainingSearchText();
        },

        findProductsContainingSearchText: function (page = 1) {
            this.showLoading();

            const params = { ...this.$router.currentRoute.query };
            params['filter[search]'] = this.getUrlParameter('search');
            params['filter[sku_or_alias_is_not]'] = this.getUrlParameter('search');
            params['filter[has_tags]'] = this.getUrlParameter('has_tags');
            params['filter[without_tags]'] = this.getUrlParameter('without_tags');
            params['include'] = 'inventory,tags,modelTags,prices,aliases,inventory.warehouse,inventoryMovementsStatistics,inventoryTotals,productDescriptions,productPicture,saleTax,assemblyProducts,assemblyProducts.simpleProduct';
            params['per_page'] = this.per_page;
            params['page'] = page;
            params['sort'] = this.getUrlParameter('sort', '-quantity');

            this.apiGetProducts(params)
                .then(({ data }) => {
                    this.products = this.products ? this.products.concat(data.data) : data.data
                    this.reachedEnd = data.data.length === 0;
                    this.pagesLoadedCount = page;

                    this.scroll_percentage = (1 - this.per_page / this.products.length) * 100;
                    this.scroll_percentage = Math.max(this.scroll_percentage, 70);

                    // Remove duplicate product if there are two products with the same sku (possible because of exact search)
                    if (this.products.length > 1 && this.products[0].sku === this.products[1].sku) {
                        this.products.shift();
                    }
                })
                .catch((error) => {
                    this.displayApiCallError(error);
                })
                .finally(() => {
                    this.hideLoading();
                });
            return this;
        },

        showNewProductModal() {
            this.$modal.showUpsertProductModal();
        },

        findProductsWithExactSku: function () {
            const params = { ...this.$router.currentRoute.query };
            params['filter[sku_or_alias]'] = this.getUrlParameter('sku') ?? this.getUrlParameter('search');
            params['include'] = 'inventory,tags,prices,aliases,inventory.warehouse,inventoryMovementsStatistics,inventoryTotals,productDescriptions,productPicture,saleTax,assemblyProducts,assemblyProducts.simpleProduct';
            params['per_page'] = 1;

            this.apiGetProducts(params)
                .then(({ data }) => {
                    if (data.data.length === 0) {
                        return;
                    }

                    this.products = this.products ? this.products.concat(data.data) : data.data

                    // Remove duplicate product if there are two products with the same sku (possible because of exact search)
                    if (this.products.length > 1 && this.products[0].sku === this.products[1].sku) {
                        this.products.shift();
                    }
                })
                .catch((error) => {
                    this.displayApiCallError(error);
                });
        },

        loadMore: function () {
            if (this.isMoreThanPercentageScrolled(this.scroll_percentage) && this.hasMorePagesToLoad() && !this.isLoading) {
                this.findProductsContainingSearchText(++this.pagesLoadedCount);
            }
        },

        hasMorePagesToLoad: function () {
            return this.reachedEnd === false;
        },

        assembleProduct(quantity = 0, productId = 0, disassemble = false) {
            const product = this.products.find(product => product.id === productId);

            if (!product.assemblyProducts || product.assemblyProducts.length === 0) {
                this.$snotify.error(this.$t('No assembly products found for this product'));
                return;
            }

            if (!quantity) {
                this.$snotify.error(this.$t('Please provide a quantity to assemble'));
                return;
            }

            const params = {
                'product_id': product.id,
                'quantity': Number(quantity),
            };

            if (disassemble) {
                this.apiDisassembleProducts(params)
                    .then(() => {
                        this.$snotify.success(this.$t('Product disassembled successfully'));
                    })
                    .catch((error) => {
                        this.displayApiCallError(error);
                    });
            } else {
                this.apiAssembleProducts(params)
                    .then(() => {
                        this.$snotify.success(this.$t('Product quantity updated successfully'));
                    })
                    .catch((error) => {
                        this.displayApiCallError(error);
                    });
            }
        },
    },
}
</script>

<style lang="scss" scoped>
.row {
    display: flex;
    justify-content: center;
    align-items: center;
}
</style>
