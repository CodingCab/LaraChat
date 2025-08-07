<template>
    <div>
        <search-and-option-bar-observer/>
        <search-and-option-bar :isStickable="true" style="z-index: 10;">
            <barcode-input-field
                placeholder="Search products using name, sku, alias or command"
                ref="barcode"
                :url_param_name="'filter[search]'"
                @barcodeScanned="findText"
            />
            <template v-slot:buttons>
                <button type="button" v-b-modal="'quick-actions-modal'" class="btn btn-primary ml-2"><font-awesome-icon icon="cog" class="fa-lg"></font-awesome-icon></button>
            </template>
        </search-and-option-bar>

        <div class="row pl-2 p-0">
            <div class="col-12 col-md-6 col-lg-6 text-nowrap text-left align-bottom pb-0 m-0 font-weight-bold text-uppercase small text-secondary">
                {{ $t('TOOLS > INVENTORY MOVEMENTS') }}
            </div>
            <div class="col-12 col-md-6 col-lg-6 text-nowrap">
                <date-selector-widget :dates="{'url_param_name': 'filter[occurred_at_between]'}"></date-selector-widget>
            </div>
        </div>

        <template v-for="record in records">
                    <inventory-movement-card :record="record" />
        </template>

        <template  v-if="isLoading === false && records !== null && records.length === 0" >
            <div class="row">
                <div class="col">
                    <div class="alert alert-info" role="alert">
                        {{ $t('No records found.') }}
                    </div>
                </div>
            </div>
        </template>

        <div class="row">
            <div class="col">
                <div ref="loadingContainerOverride" style="height: 32px"></div>
            </div>
        </div>

        <b-modal id="quick-actions-modal" no-fade hide-header @hidden="setFocusElementById('barcode_input')">
            <stocktake-input></stocktake-input>
            <template #modal-footer>
                <b-button variant="secondary" class="float-right" @click="$bvModal.hide('quick-actions-modal');">
                    {{ $t('Cancel') }}
                </b-button>
                <b-button variant="primary" class="float-right" @click="$bvModal.hide('quick-actions-modal');">
                    {{ $t('OK') }}
                </b-button>
            </template>
        </b-modal>
    </div>
</template>

<script>
    import loadingOverlay from '../mixins/loading-overlay';
    import ProductCard from "./Products/ProductCard";
    import BarcodeInputField from "./SharedComponents/BarcodeInputField";
    import InventoryMovementCard from './SharedComponents/InventoryMovementCard';
    import url from "../mixins/url";
    import api from "../mixins/api";
    import helpers from "../mixins/helpers";

    export default {
        mixins: [loadingOverlay, url, api, helpers],

        components: {
            ProductCard,
            BarcodeInputField,
            InventoryMovementCard
        },

        data: function() {
            return {
                per_page: 25,
                pagesLoaded: 0,
                reachedEnd: false,

                records: null,
            };
        },

        watch: {
            '$route' (to, from) {
                if (to.path === from.path) {
                    this.$nextTick(() => {
                        this.loadRecords(1);
                    });
                }
            }
        },

        mounted() {
            if (this.currentUser()['warehouse']) {
                this.getUrlFilterOrSet('filter[warehouse_code]', this.currentUser()['warehouse']['code']);
            }

            window.onscroll = () => this.loadMore();

            this.loadRecords(1);
        },

        methods: {
            findText(search) {
                this.setUrlParameter('filter[occurred_at_between]', '');
                this.setUrlParameter('filter[description]', '');
                this.setUrlParameter('search', search);
                this.getUrlFilterOrSet('sort', '-occurred_at,-sequence_number');
                this.reloadProducts();
            },

            reloadProducts() {
                this.products = [];
                console.log('reloading products');
                this.loadRecords();
            },

            loadRecords: function(page = 1) {
                this.showLoading();

                if ((this.perPage < 100) && (this.pagesLoaded % 2 === 0)) {
                    this.perPage = this.perPage * 2;
                    this.pagesLoaded = this.pagesLoaded / 2;
                }

                let params = this.$router.currentRoute.query;
                params['include'] = 'product,inventory,user,product.tags';
                params['sort'] = this.getUrlFilterOrSet('sort', '-occurred_at,-sequence_number');
                params['page'] = page;
                params['per_page'] = this.perPage;

                this.apiGetInventoryMovements(params)
                    .then(({data}) => {
                        if (page === 1) {
                            this.records = [];
                        }

                        this.reachedEnd = data.data.length === 0;
                        this.pagesLoaded = page;

                        this.records = this.records.concat(data.data);
                    })
                    .finally(() => {
                        this.hideLoading();
                    });
            },

            loadMore: function () {
                if (this.isLoading) {
                    return;
                }

                if (this.reachedEnd) {
                    return;
                }

                if (! this.isMoreThanPercentageScrolled(70)) {
                    return;
                }

                this.loadRecords(++this.pagesLoaded);
            },

            hasMorePagesToLoad: function () {
                return this.lastPage > this.lastPageLoaded;
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

    tr {
        padding: 0 !important;
        margin: 0 !important;
    }

    td {
        padding-top: 0 !important;
        padding-bottom: 0 !important;
        margin-bottom: 0 !important;
        margin-top: 0 !important;
    }
</style>
