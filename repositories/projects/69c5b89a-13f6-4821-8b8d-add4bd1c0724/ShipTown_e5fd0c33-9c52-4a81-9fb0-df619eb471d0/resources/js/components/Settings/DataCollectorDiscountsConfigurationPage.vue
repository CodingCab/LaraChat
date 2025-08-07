<template>
    <div>
        <search-and-option-bar-observer />
        <search-and-option-bar :isStickable="true">
            <barcode-input-field :input_id="'discount_code_input'" :placeholder="$t('Search')" ref="barcode" :url_param_name="'search'"
                @refreshRequest="reloadDiscountsList" @barcodeScanned="findText" />
            <template v-slot:buttons>
                <button @click="showNewDiscountModal" type="button" class="btn btn-primary ml-2">
                    <font-awesome-icon icon="plus" class="fa-lg"></font-awesome-icon>
                </button>
            </template>
        </search-and-option-bar>

        <div class="row pl-2 p-0">
            <breadcrumbs></breadcrumbs>
        </div>

        <template v-if="isLoading === false && discounts !== null && Array.isArray(discounts) && discounts.length === 0">
            <div class="text-secondary small text-center mt-3">
                {{ $t('No records found') }}<br>
                {{ $t('Click + to create one') }}<br>
            </div>
        </template>

        <template v-if="discounts">
            <swiping-card v-for="discount in discounts" :key="discount.id" disable-swipe-right disable-swipe-left>
                <template v-slot:content>
                    <div role="button" class="row">
                        <div class="col-12 col-md-9">
                            <div class="text-primary">{{ discount['code'] }}</div>
                            <div class="text-secondary small">
                                {{ $t('Discount') }}: {{ discount['percentage_discount'] }}%
                            </div>
                        </div>
                        <div class="col-12 col-md-3 d-flex align-items-center justify-content-end">
                            <button class="remove-button d-inline-flex align-items-center justify-content-center"
                                @click="removeDiscount(discount['id'])">
                                <font-awesome-icon icon="trash" class="fa-lg"></font-awesome-icon>
                            </button>
                        </div>
                    </div>
                </template>
            </swiping-card>
        </template>

        <div class="row">
            <div class="col">
                <div ref="loadingContainerOverride" style="height: 32px"></div>
            </div>
        </div>
    </div>
</template>

<script>
import loadingOverlay from "../../mixins/loading-overlay";
import url from "../../mixins/url.vue";
import api from "../../mixins/api.vue";
import helpers from "../../mixins/helpers";
import Breadcrumbs from "../Reports/Breadcrumbs.vue";
import BarcodeInputField from "../SharedComponents/BarcodeInputField.vue";
import SwipingCard from "../SharedComponents/SwipingCard.vue";
import Modals from "../../plugins/Modals";

export default {
    mixins: [loadingOverlay, url, api, helpers],

    components: {
        Breadcrumbs,
        BarcodeInputField,
        SwipingCard,
    },

    data: () => ({
        pagesLoadedCount: 1,
        reachedEnd: false,
        discounts: null,
        per_page: 20,
        scroll_percentage: 70,
    }),

    mounted() {
        window.onscroll = () => this.loadMore();

        Modals.EventBus.$on('hide::modal::module-data-collector-discounts-new-discount-modal', () => {
            this.reloadDiscountsList();
        });

        this.reloadDiscountsList();
    },

    methods: {
        findText(search) {
            this.setUrlParameter('search', search);
            this.reloadDiscountsList();
        },

        reloadDiscountsList() {
            this.discounts = null;

            this.findDiscounts();
        },

        findDiscounts(page = 1) {
            this.showLoading();

            const params = { ...this.$router.currentRoute.query };
            params['filter[search]'] = this.getUrlParameter('search') ?? '';
            params['per_page'] = this.per_page;
            params['page'] = page;

            this.apiGetDiscounts(params)
                .then(({ data }) => {
                    this.discounts = this.discounts ? this.discounts.concat(data.data) : data.data
                    this.reachedEnd = data.data.length === 0;
                    this.pagesLoadedCount = page;

                    this.scroll_percentage = (1 - this.per_page / this.discounts.length) * 100;
                    this.scroll_percentage = Math.max(this.scroll_percentage, 70);
                })
                .catch((error) => {
                    this.displayApiCallError(error);
                })
                .finally(() => {
                    this.hideLoading();
                });

            return this;
        },

        showNewDiscountModal() {
            this.$modal.showNewDiscountModal();
        },

        loadMore() {
            if (this.isMoreThanPercentageScrolled(this.scroll_percentage) && this.hasMorePagesToLoad() && !this.isLoading) {
                this.findDiscounts(++this.pagesLoadedCount);
            }
        },

        hasMorePagesToLoad() {
            return this.reachedEnd === false;
        },

        removeDiscount(id) {
            this.showLoading();

            this.apiDeleteDiscount(id)
                .then(() => {
                    this.reloadDiscountsList();
                    this.$snotify.success(this.$t('Discount removed'));
                })
                .catch((error) => {
                    this.displayApiCallError(error);
                })
                .finally(() => {
                    this.hideLoading();
                });
        },
    },
}
</script>

<style lang="scss" scoped>
.remove-button {
    cursor: pointer;
    width: 40px;
    height: 40px;
    border: 1px solid #dc3545;
    color: #dc3545;
    background-color: transparent;
    transition: background-color 0.3s, color 0.3s;

    &:hover {
        background-color: #dc3545;
        color: white;
    }
}
</style>
