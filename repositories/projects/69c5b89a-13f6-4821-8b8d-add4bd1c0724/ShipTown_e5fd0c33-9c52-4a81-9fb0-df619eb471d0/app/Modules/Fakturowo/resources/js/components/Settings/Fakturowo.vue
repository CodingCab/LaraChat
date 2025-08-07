<template>
    <div>
        <search-and-option-bar-observer />
        <search-and-option-bar :isStickable="true">
            <barcode-input-field :input_id="'discount_code_input'" placeholder="Search" ref="barcode" :url_param_name="'search'"
                                 @refreshRequest="reloadConfigurations" @barcodeScanned="findText" />
            <template v-slot:buttons>
                <button @click="showNewConfigurationModal" type="button" class="btn btn-primary ml-2">
                    <font-awesome-icon icon="plus" class="fa-lg"></font-awesome-icon>
                </button>
            </template>
        </search-and-option-bar>

        <div class="row pl-2 p-0">
            <breadcrumbs></breadcrumbs>
        </div>

        <template v-if="isLoading === false && configurations !== null && Array.isArray(configurations) && configurations.length === 0">
            <div class="text-secondary small text-center mt-3">
                {{ $t('No configurations found') }}<br>
                {{ $t('Click + to create one') }}<br>
            </div>
        </template>

        <template v-if="configurations">
            <swiping-card v-for="config in configurations" :key="config.id" disable-swipe-right disable-swipe-left>
                <template v-slot:content>
                    <div role="button" class="row">
                        <div class="col-12 col-md-9">
                            <div class="text-primary">{{ config['connection_code'] }}</div>
                        </div>
                        <div class="col-12 col-md-3 d-flex align-items-center justify-content-end">
                            <button class="remove-button d-inline-flex align-items-center justify-content-center"
                                    @click="removeConfig(config['id'])">
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
import loadingOverlay from "../../../../../../../resources/js/mixins/loading-overlay";
import url from "../../../../../../../resources/js/mixins/url.vue";
import api from "../../../../../../../resources/js/mixins/api.vue";
import helpers from "../../../../../../../resources/js/mixins/helpers";
import Breadcrumbs from "../../../../../../../resources/js/components/Reports/Breadcrumbs.vue";
import BarcodeInputField from "../../../../../../../resources/js/components/SharedComponents/BarcodeInputField.vue";
import SwipingCard from "../../../../../../../resources/js/components/SharedComponents/SwipingCard.vue";
import Modals from "../../../../../../../resources/js/plugins/Modals";

export default {
    mixins: [loadingOverlay, url, api, helpers],

    components: {
        Breadcrumbs,
        BarcodeInputField,
        SwipingCard,
    },

    data() {
        return {
            pagesLoadedCount: 1,
            configurations: null,
            perPage: 20,
            scrollPercentage: 70,
        }
    },

    mounted() {
        window.onscroll = () => this.loadMore();

        Modals.EventBus.$on('hide::modal::module-fakturowo-new-configuration-modal', () => {
            this.reloadConfigurations();
        });

        this.reloadConfigurations();
    },

    methods: {
        findText(search) {
            this.setUrlParameter('search', search);
            this.reloadConfigurations();
        },

        reloadConfigurations() {
            this.configurations = null;

            this.findConfigurations();
        },

        findConfigurations(page = 1) {
            this.showLoading();

            const params = { ...this.$router.currentRoute.query };
            params['filter[search]'] = this.getUrlParameter('search') ?? '';
            params['per_page'] = this.perPage;
            params['page'] = page;

            this.apiGetFakturowoConfiguration(params)
                .then(({data}) => {
                    this.configurations = this.configurations ? this.configurations.concat(data.data) : data.data
                    this.reachedEnd = data.data.length === 0;
                    this.pagesLoadedCount = page;

                    this.scrollPercentage = (1 - this.perPage / this.configurations.length) * 100;
                    this.scrollPercentage = Math.max(this.scrollPercentage, 70);
                })
                .catch(e => {
                    this.displayApiCallError(e);
                    this.hideLoading();
                })
                .finally(() => {
                    this.hideLoading();
                });
        },

        loadMore() {
            if (this.isMoreThanPercentageScrolled(this.scrollPercentage) && this.hasMorePagesToLoad() && !this.isLoading) {
                this.findConfigurations(++this.pagesLoadedCount);
            }
        },

        showNewConfigurationModal() {
            this.$modal.showNewConfigurationModal();
        },

        hasMorePagesToLoad() {
            return this.reachedEnd === false;
        },

        removeConfig(id) {
            this.showLoading();

            this.apiDeleteFakturowoConfiguration(id)
                .then(() => {
                    this.reloadConfigurations();
                    this.$snotify.success(this.$t('Configuration removed'));
                })
                .catch((error) => {
                    this.displayApiCallError(error);
                })
                .finally(() => {
                    this.hideLoading();
                });
        }
    },
}
</script>

<style scoped lang="scss">
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
