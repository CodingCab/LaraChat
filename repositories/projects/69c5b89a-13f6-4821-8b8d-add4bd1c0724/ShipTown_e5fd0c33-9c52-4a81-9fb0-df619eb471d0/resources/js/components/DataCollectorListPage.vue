<template>
    <div>
        <search-and-option-bar-observer />
        <search-and-option-bar :isStickable="true">
            <barcode-input-field :placeholder="$t('Search')" :url_param_name="'filter[name_contains]'"
                @barcodeScanned="loadData(1)"></barcode-input-field>
            <template v-slot:buttons>
                <b-dropdown dropleft no-caret variant="primary" class="ml-2" dusk="new_data_collection" id="new_data_collection">
                    <template #button-content>
                        <font-awesome-icon icon="plus" class="fa-lg"></font-awesome-icon>
                    </template>
                    <b-dropdown-item @click="createTransferIn">{{ $t('Transfer In') }}</b-dropdown-item>
                    <b-dropdown-item @click="createTransferOut">{{ $t('Transfer Out') }}</b-dropdown-item>
                    <b-dropdown-item @click="createOfflineInventory">{{ $t('Offline Inventory') }}</b-dropdown-item>
                    <b-dropdown-item @click="createStocktake">{{ $t('Stocktake') }}</b-dropdown-item>
                    <b-dropdown-item @click="createPurchaseOrder">{{ $t('Purchase Order') }}</b-dropdown-item>
                    <b-dropdown-divider></b-dropdown-divider>
                    <b-dropdown-item @click="createBlankCollection" name="create_blank_collection_button"
                        dusk="create_blank_collection_button" id="create_blank_collection_button">{{ $t('Blank') }}
                    </b-dropdown-item>
                </b-dropdown>
            </template>
        </search-and-option-bar>

        <div class="row pl-2 p-0 text-uppercase text-secondary">
            <div class="col-8 text-nowrap text-left align-bottom">
                <breadcrumbs></breadcrumbs>
            </div>
            <div class="col-4 text-nowrap">
                <div class="custom-control custom-switch m-auto text-right align-bottom small">
                    <input type="checkbox" @change="toggleArchivedFilter" class="custom-control-input" id="switch" v-model="showArchived">
                    <label class="custom-control-label" for="switch">{{ $t('Archived') }}</label>
                </div>
            </div>
        </div>

        <div v-if="(data !== null) && (data.length === 0)" class="text-secondary small text-center mt-3">
            {{ $t('No records found') }}<br>
            {{ $t('Click + to create one') }}<br>
        </div>

        <swiping-card v-for="record in data" disable-swipe-right disable-swipe-left :key="record['id']">
            <template v-slot:content>
                <div role="button" dusk="data_collection_record" class="row" @click="openDataCollection(record['id'])">
                    <div class="col-sm-12 col-lg-6">
                        <div class="text-primary">{{ record['name'] }}</div>
                        <div class="text-secondary small">
                            {{ formatDateTime(record['created_at'], 'dddd - MMM D HH:mm') }}
                        </div>
                        <div class="text-secondary small">{{ collectionTypes[record['type']] }}</div>
                    </div>
                    <div class="col-cols col-sm-12 col-lg-6 bottom text-right">
                        <text-card v-if="record['deleted_at'] !== null" :label="formatDateTime(record['deleted_at'], 'dddd - MMM D HH:mm')"
                            :text="$t('ARCHIVED')" class="float-left text-left"></text-card>
                        <text-card :label="$t('warehouse')" :text="record['warehouse_code']"></text-card>
                        <number-card :label="$t('differences')" :number="record['differences_count']"></number-card>
                    </div>
                </div>
            </template>
        </swiping-card>

        <div class="row">
            <div class="col">
                <div ref="loadingContainerOverride" style="height: 32px"></div>
            </div>
        </div>

        <b-modal id="new-collection-modal" no-fade hide-header title="New Data Collection" @ok="createCollectionAndRedirect"
            @shown="prepareNewCollectionModal">
            <input dusk="collection_name_input" id="collection_name_input" v-model="newCollectionName" type="text"
                @keyup.enter="createCollectionAndRedirect" class="form-control" :placeholder="$t('New Collection name')">
            <hr>

            <button id="import-csv-button" type="button" class="col btn mb-1 btn-primary" @click="openCsvImportModal">
                {{ $t('Import CSV File') }}
            </button>
        </b-modal>

        <options-modal id="csv-import-modal" :title="$t('Import CSV File')" :show-stocktake-input="false">
            <csv-import @fileUpload="postCsvRecordsToApiAndCloseModal" v-model="csv" headers canIgnore autoMatchFields :map-fields="map_fields"></csv-import>
        </options-modal>

        <b-modal id="configuration-modal" autofocus centered no-fade title="Data Collection">
            <button type="button" @click.prevent="downloadFile" class="col btn mb-1 btn-primary">{{ $t('Download') }}</button>
            <template #modal-footer>
                <b-button variant="secondary" class="float-right" @click="$bvModal.hide('configuration-modal');">
                    {{ $t('Cancel') }}
                </b-button>
            </template>
        </b-modal>
    </div>
</template>

<script>
import beep from '../mixins/beep';
import loadingOverlay from '../mixins/loading-overlay';

import FiltersModal from "./Packlist/FiltersModal";
import url from "../mixins/url";
import api from "../mixins/api";
import helpers from "../mixins/helpers";
import Vue from "vue";
import NumberCard from "./SharedComponents/NumberCard";
import SwipingCard from "./SharedComponents/SwipingCard";
import Breadcrumbs from "./Reports/Breadcrumbs.vue";
import OptionsModal from "./OptionsModal.vue";

export default {
    mixins: [loadingOverlay, beep, url, api, helpers],

    components: {
        Breadcrumbs,
        FiltersModal,
        NumberCard,
        SwipingCard,
        OptionsModal,
    },

    data: function () {
        return {
            showArchived: false,
            map_fields: [],
            csv: {},
            data: null,
            reachedEnd: false,
            per_page: 50,
            page: 1,
            newCollectionName: null,
            newCollectionType: null,
            newCollectionDestinationWarehouseID: null,
            collectionTypes: {
                'App\\Models\\DataCollectionTransferIn': this.$t('Transfer In'),
                'App\\Models\\DataCollectionTransferOut': this.$t('Transfer Out'),
                'App\\Models\\DataCollectionStocktake': this.$t('Stocktake'),
                'App\\Models\\DataCollectionOfflineInventory': this.$t('Offline Inventory'),
                'App\\Models\\DataCollectionPurchaseOrder': this.$t('Purchase Order'),
                'App\\Models\\DataCollectionTransaction': this.$t('Transaction'),
            },
        };
    },


    watch: {
        '$route' (to, from) {
            if (to.path === from.path) {
                this.$nextTick(() => {
                    this.loadData();
                });
            }
        }
    },

    mounted() {
        if (!Vue.prototype.$currentUser['warehouse_id']) {
            this.$snotify.error(this.$t('You do not have warehouse assigned. Please contact administrator'), { timeout: 50000 });
            return;
        }

        this.getUrlFilterOrSet('filter[warehouse_code]', Vue.prototype.$currentUser['warehouse']['code']);
        this.showArchived = this.getUrlFilterOrSet('filter[only_archived]', 'false') === 'true';

        window.onscroll = () => this.loadMoreWhenNeeded();


        this.loadData();

        this.apiGetWarehouses()
            .then(response => {
                this.map_fields = ['product_sku'].concat(response.data.data.map(warehouse => warehouse.code));
            });
    },

    methods: {
        createTransferIn() {
            this.newCollectionType = 'App\\Models\\DataCollectionTransferIn';
            this.newCollectionDestinationWarehouseID = this.currentUser()['warehouse_id'];
            this.$bvModal.show('new-collection-modal');
        },

        createTransferOut() {
            this.newCollectionType = 'App\\Models\\DataCollectionTransferOut';
            this.newCollectionDestinationWarehouseID = null;
            this.$bvModal.show('new-collection-modal');
        },

        createOfflineInventory() {
            this.newCollectionType = 'App\\Models\\DataCollectionOfflineInventory';
            this.newCollectionDestinationWarehouseID = null;
            this.$bvModal.show('new-collection-modal');
        },

        createPurchaseOrder() {
            this.newCollectionType = 'App\\Models\\DataCollectionPurchaseOrder';
            this.newCollectionDestinationWarehouseID = null;
            this.$bvModal.show('new-collection-modal');
        },

        createStocktake() {
            this.newCollectionType = 'App\\Models\\DataCollectionStocktake';
            this.newCollectionDestinationWarehouseID = null;
            this.$bvModal.show('new-collection-modal');
        },

        createBlankCollection() {
            this.newCollectionType = null;
            this.newCollectionDestinationWarehouseID = null;
            this.$bvModal.show('new-collection-modal');
        },

        toggleArchivedFilter(event) {
            this.setUrlParameter('filter[only_archived]', event.target.checked);
            this.loadData();
        },

        openCsvImportModal() {
            this.$bvModal.hide('new-collection-modal');
            this.$nextTick(() => this.$bvModal.show('csv-import-modal'));
        },

        postCsvRecordsToApiAndCloseModal() {
            let arg = this.csv.csv;

            if (Array.isArray(arg)) {
                arg.shift();
            }
            const payload = {
                'data_collection_name_prefix': this.newCollectionName,
                'data': arg,
            }
            this.apiPostCsvImportDataCollections(payload)
                .then(() => {
                    this.notifySuccess(this.$t('Records imported'));
                    this.$bvModal.hide('configuration-modal');
                })
                .catch(e => {
                    this.displayApiCallError(e);
                })
                .finally(() => {
                    this.loadData();
                });

            this.$bvModal.hide('new-collection-modal');
            this.$bvModal.hide('csv-import-modal');
        },

        prepareNewCollectionModal() {
            this.csv = null;
            this.newCollectionName = null;
            this.setFocusElementById('collection_name_input', true);
        },

        openDataCollection(data_collection_id) {
            window.location.href = '/data-collector/' + data_collection_id;
        },

        createCollectionAndRedirect(event) {
            const payload = {
                'warehouse_id': this.currentUser()['warehouse_id'],
                'warehouse_code': this.currentUser()['warehouse_code'],
                'name': this.newCollectionName,
                'type': this.newCollectionType,
                'destination_warehouse_id': this.newCollectionDestinationWarehouseID,
            }

            this.apiPostDataCollection(payload)
                .then(() => {
                    this.notifySuccess(this.$t('Data collected'));
                    this.$bvModal.hide('new-collection-modal');
                })
                .catch(e => {
                    this.displayApiCallError(e);
                })
                .finally(() => {
                    this.loadData();
                });
        },

        loadMoreWhenNeeded() {
            if (this.isLoading) {
                return;
            }

            if (this.isMoreThanPercentageScrolled(70) === false) {
                return;
            }

            if (this.reachedEnd === true) {
                return;
            }

            this.loadData(++this.page);
        },

        loadData(page = 1) {
            this.showLoading();

            const params = this.$router.currentRoute.query;
            params['sort'] = this.getUrlParameter('sort', '-created_at');
            params['per_page'] = this.getUrlParameter('per_page', this.per_page);
            params['page'] = page;

            if (params['filter[without_transactions]']) {
                params['filter[without_transactions]'] = true;
            }

            this.apiGetDataCollector(params)
                .then((response) => {
                    this.reachedEnd = response.data.data.length === 0;

                    if (page === 1) {
                        this.data = response.data.data;
                    } else {
                        this.data = this.data.concat(response.data.data);
                    }
                })
                .catch((error) => {
                    this.displayApiCallError(error);
                })
                .finally(() => {
                    this.hideLoading();
                });
        },

        downloadFile() {
            let routeData = this.$router.resolve({
                path: this.$router.currentRoute.fullPath,
                query: { filename: "DataCollections.csv" }
            });
            window.open(routeData.href, '_blank');
        },
    },
}
</script>


<style lang="scss">
.setting-list:hover,
.setting-list:focus {
    color: #495057;
    text-decoration: none;
    background-color: #f8f9fa;
}
</style>
