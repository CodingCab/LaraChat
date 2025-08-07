<template>
    <div v-if="dataCollection">
        <template v-if="dataCollection && dataCollection['currently_running_task'] != null">
            <div class="alert alert-danger">{{ $t('Please wait while stock being updated') }}</div>
        </template>

        <swiping-card :disable-swipe-right="true" :disable-swipe-left="true">
            <template v-slot:content>
                <div class="row setting-list">
                    <div class="col-sm-12 col-lg-2">
                        <div id="data_collection_name" class="text-primary">{{ dataCollection['name'] }}</div>
                        <div class="text-secondary small">
                            {{ formatDateTime(dataCollection['created_at'], 'dddd - MMM D HH:mm') }}
                        </div>
                        <div class="text-secondary small">{{ collectionTypes[dataCollection['type']] }}</div>
                    </div>

                    <div class="col-sm-12 col-lg-5">
                        <div class="row">
                            <div v-if="dataCollection['billing_address']" class="col-sm-12 col-lg-6">
                                <billing-address :data-collection="dataCollection"/>
                            </div>
                            <div v-if="dataCollection['shipping_address']" class="col-sm-12 col-lg-6">
                                <shipping-address :data-collection="dataCollection"/>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-lg-4 text-right text-nowrap text-right">
                        <div class="d-none d-sm-inline-block">
                            <number-card min-width="70px" :label="'quantity'" :number="dataCollection && dataCollection['total_quantity_scanned']"></number-card>
                        </div>
                        <number-card class="small" min-width="90px" :label="$t('total to pay')" :number="dataCollection && dataCollection['total_sold_price']"></number-card>
                        <number-card class="small" min-width="90px" :label="$t('total tax')" :number="dataCollection && dataCollection['total_tax']"></number-card>
                        <number-card class="small" min-width="90px" :label="$t('total paid')" :number="dataCollection && dataCollection['total_paid']" id="total_paid"></number-card>
                        <number-card class="small" min-width="90px" :label="$t('total outstanding')" :number="dataCollection && (dataCollection['total_outstanding'] ?? dataCollection['total_sold_price'])"></number-card>
                        <text-card text="ARCHIVED"
                            :label="formatDateTime(dataCollection ? dataCollection['deleted_at'] : '', 'dddd - MMM D HH:mm')"
                            v-if="dataCollection && dataCollection['deleted_at']"></text-card>
                    </div>
                </div>
            </template>
        </swiping-card>

        <search-and-option-bar-observer />
        <search-and-option-bar :isStickable="true">
            <div class="d-flex flex-nowrap">
                <div class="flex-fill">
                    <barcode-input-field :input_id="'barcode_input'" :showManualSearchButton="true" @barcodeScanned="onBarcodeScanned" :placeholder="$t('Scan sku or alias')" class="text-center font-weight-bold"></barcode-input-field>
                </div>
            </div>
            <template v-slot:buttons>
                <top-nav-button v-b-modal="'optionsModal'" id="options-button" />
                <button id="pay-button" class="btn btn-primary ml-2" @click.prevent="selectPayment">{{ $t('PAY') }}</button>
            </template>
        </search-and-option-bar>

        <div v-show="manuallyExpandComments" class="row mb-2 mt-1 my-1">
            <input id="comment-input" ref="newCommentInput" v-model="input_comment" class="form-control" :placeholder="$t('Add comment here')"
                @keypress.enter="addComment" />
        </div>

        <div class="mb-1" v-if="commentsToShow.length">
            <div class="d-flex mx-1" v-for="(comment, index) in commentsToShow" @click="toggleExpandComments" :key="comment.id">
                <div>
                    <b>{{ comment.user ? comment.user.name : (comment.is_customer ? 'Customer' : 'AutoPilot') }}: </b>{{ comment.comment }}
                </div>
                <div class="ml-auto" v-if="index === 0">
                    <font-awesome-icon v-if="manuallyExpandComments" icon="chevron-up" class="fa fa-xs"></font-awesome-icon>
                    <font-awesome-icon v-if="!manuallyExpandComments" icon="chevron-down" class="fa fa-xs"></font-awesome-icon>
                </div>
            </div>
        </div>
        <div v-else class="row text-center text-secondary" @click="toggleExpandComments">
            <div class="col">
                <font-awesome-icon v-if="manuallyExpandComments" icon="chevron-up" class="fa fa-xs"></font-awesome-icon>
                <font-awesome-icon v-if="!manuallyExpandComments" icon="chevron-down" class="fa fa-xs"></font-awesome-icon>
            </div>
        </div>

        <data-collector-quantity-request-modal @hidden="onQuantityRequestModalHidden"></data-collector-quantity-request-modal>

        <div v-if="(dataCollectionRecords !== null) && (dataCollectionRecords.length === 0)" class="text-secondary small text-center mt-3">
            {{ $t('No records found') }}<br>
            {{ $t('Scan or type in SKU to start') }}<br>
        </div>

        <swiping-card v-for="record in dataCollectionRecords" :key="record.id" disable-swipe-right disable-swipe-left>
            <template v-slot:content>
                <div class="row">
                    <div class="col-12 col-md-4">
                        <product-info-card :product="record['product']" :show-tags="true" :showProductDescriptions="false"></product-info-card>
                    </div>
                    <div class="col-12 col-md-3 text-left small">
                        <div>{{ $t('in stock') }}:
                            <strong>{{ dashIfZero(Number(record['inventory']['quantity'])) }}</strong>
                        </div>
                        <div v-if="record['price_source'] !== 'FULL_PRICE'">{{ $t('full price') }}:
                            <strong>{{ dashIfZero(Number(record['unit_full_price'])) }}</strong>
                        </div>
                        <div v-if="record['price_source'] && record['price_source'] !== 'FULL_PRICE'">{{ $t('price source') }}:
                            <strong>{{ record['price_source'] }}</strong>
                        </div>
                        <div v-if="record['price_source'] && record['price_source'] === 'DISCOUNT_CODE'">
                            {{ $t('discount code') }}:
                            <strong>{{ dataCollection['billing_address']['discount_code'] ?? '' }}</strong>
                        </div>
                        <div v-if="record['discount']">{{ $t('discount name') }}:
                            <strong>{{ record['discount']['name'] ?? '' }}</strong>
                        </div>
                        <div>{{ $t('comment') }}:
                            <strong v-if="record['comment']" @click="toggleCommentInput(record)">{{ record['comment'] }}</strong>
                            <span v-else class="text-secondary" @click="toggleCommentInput(record)">{{ $t('Add comment') }}</span>
                            <div v-show="isCommentInputVisible(record)">
                                <input :id="`comment-input-${record['id']}`" type="text" v-model="newComment" class="form-control"
                                    @keypress.enter="addCommentToRecord(record)">
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-5 text-right d-flex flex-column justify-content-between">
                        <div>
                            <div @click="updateQuantity(record)" class="d-inline-block">
                                <number-card min-width="90px" :label="$t('quantity')" :number="record['quantity_scanned']"
                                    v-bind:class="{ 'bg-warning': record['quantity_scanned'] > 0 && record['quantity_requested'] && record['quantity_requested'] < record['quantity_scanned'] + record['total_transferred_out'] + record['total_transferred_in'] }"></number-card>
                            </div>
                            <div @click="updateUnitPrice(record)" class="d-inline-block">
                                <number-card min-width="90px" :label="$t('unit price')" :number="record['unit_sold_price']"
                                    v-bind:class="{ 'bg-warning': record['unit_discount'] !== 0 }"></number-card>
                            </div>
                            <span class=" disabled">

                            <number-card min-width="90px" :label="$t('total tax')" :number="record['total_tax']"></number-card>
                            </span>
                            <number-card min-width="90px" :label="$t('total price')" :number="record['total_price']"></number-card>
                        </div>
                    </div>
                </div>
            </template>
        </swiping-card>

        <div class="row">
            <div class="col">
                <div ref="loadingContainerOverride" style="height: 32px"></div>
            </div>
        </div>

        <options-modal>
            <div v-if="dataCollection">
                <div v-if="dataCollection['deleted_at'] === null" :class="{ 'disabled': true }">
                    <button id="select-customer-button" :disabled="!buttonsEnabled" @click.prevent="selectCustomer" v-b-toggle class="col btn mb-2 btn-primary">{{ $t('Select Customer') }}</button>
                    <br>
                    <button id="print-receipt-button" :disabled="!buttonsEnabled" @click.prevent="printReceipt" v-b-toggle class="col btn mb-2 btn-primary">{{ $t('Print Receipt') }}</button>
                    <button id="preview-receipt-button" :disabled="!buttonsEnabled" @click.prevent="previewReceipt" v-b-toggle class="col btn mb-2 btn-primary">{{ $t('Preview Receipt') }}</button>
                    <button id="email-receipt-button" :disabled="!buttonsEnabled" @click.prevent="emailPdfReceipt" v-b-toggle class="col btn mb-2 btn-primary">{{ $t('Email Receipt') }}</button>
                    <hr>
                    <button id="hold-transaction-button" :disabled="!buttonsEnabled" @click.prevent="saveTransaction" v-b-toggle class="col btn mb-2 btn-primary">{{ $t('Hold Transaction') }}</button>
                    <button id="cancel-transaction-button" :disabled="!buttonsEnabled" @click.prevent="archiveTransaction" v-b-toggle class="col btn mb-2 btn-primary">{{ $t('Cancel Transaction') }}</button>
                    <hr>
                    <div v-if="selectedPrinter" class="row mb-2">
                        <div class="col">
                            <div class="setting-title">{{ $t('Selected Printer') }}</div>
                            <div class="setting-desc">{{ selectedPrinter.name }}</div>
                        </div>
                    </div>
                    <button :disabled="!buttonsEnabled" @click.prevent="selectPrinter" v-b-toggle class="col btn mb-2 btn-primary">
                        <template v-if="selectedPrinter">{{ $t('Change printer') }}</template>
                        <template v-else>{{ $t('Select printer') }}</template>
                    </button>
                    <!--                    <button id="auto-scan-all-records-button" :disabled="!buttonsEnabled" @click.prevent="autoScanAll" v-b-toggle class="col btn mb-2 btn-primary">AutoScan ALL Records</button>-->
                    <!--                    <button id="transferInButton" :disabled="!buttonsEnabled" @click.prevent="transferStockIn" v-b-toggle-->
                    <!--                        class="col btn mb-2 btn-primary">Transfer In-->
                    <!--                    </button>-->
                    <!--                    <button :disabled="!buttonsEnabled" @click.prevent="transferToWarehouseClick" v-b-toggle-->
                    <!--                        class="col btn mb-2 btn-primary">Transfer To...-->
                    <!--                    </button>-->
                </div>
                <br>
                <a :href="getDownloadLink" @click.prevent="downloadFileAndHideModal" v-b-toggle
                    class="col btn mb-1 btn-primary">{{ $t('Download') }}</a>
                <div v-if="dataCollection['deleted_at'] === null">
                    <hr>
                    <!--                    <import-csv-file-component :map_fields="['product_sku', 'quantity_requested', 'quantity_scanned']" :csv="csv" :post-csv-records-to-api-and-close-modal="postCsvRecordsToApiAndCloseModal"/>-->
                </div>


                <div class="row mb-2">
                    <div class="col">
                        <div class="setting-title">{{ $t('Single Scan mode') }}</div>
                        <div class="setting-desc">
                            {{ $t('It will not ask for quantity when scanned') }} <br>
                            {{ $t('1 will be used as default') }}
                        </div>
                    </div>
                    <div class="custom-control custom-switch m-auto text-right align-content-center float-right w-auto">
                        <input type="checkbox" @change="toggleSingleScanMode" class="custom-control-input" id="singleScanToggle"
                            v-model="singleScanEnabled">
                        <label class="custom-control-label" for="singleScanToggle"></label>
                    </div>
                </div>
                <hr>
                <div class="row mb-2">
                    <div class="col">
                        <div class="setting-title">{{ $t('Scan into Quantity Requested') }}</div>
                        <div class="setting-desc">
                            {{ $t('When product scanned, quantity requested will be amended') }} <br>
                            {{ $t('instead of quantity scanned') }}
                        </div>
                    </div>
                    <div class="custom-control custom-switch m-auto text-right align-content-center float-right w-auto">
                        <input type="checkbox" @change="toggleAddToRequested" class="custom-control-input" id="toggleAddToRequested"
                            v-model="addToRequested">
                        <label class="custom-control-label" for="toggleAddToRequested"></label>
                    </div>
                </div>
            </div>
        </options-modal>

        <b-modal id="transferToModal" no-fade hide-header @hidden="setFocusElementById('barcode_input')">
            <template v-for="warehouse in warehouses">
                <button v-if="dataCollection && warehouse['id'] !== dataCollection['warehouse_id']"
                    @click.prevent="transferToWarehouse(warehouse)" v-b-toggle class="col btn mb-2 btn-primary" :key="warehouse.id">
                    {{ warehouse.name }}
                </button>
            </template>

            <template #modal-footer>
                <b-button variant="secondary" class="float-right" @click="$bvModal.hide('transferToModal');">Cancel</b-button>
            </template>
        </b-modal>

        <set-transaction-printer-modal />
        <find-address-modal :transaction-details="dataCollection" />
        <new-address-modal />
        <data-collection-choose-payment-type-modal :details="dataCollection" />
        <data-collection-add-payment-modal />
        <data-collection-transaction-status-modal :details="dataCollection" :printer="selectedPrinter" />
        <data-collection-record-update-quantity-modal />
        <data-collection-record-update-unit-price-modal />
        <data-collection-preview-transaction-receipt-modal />
    </div>
</template>

<script>
import beep from '../../../mixins/beep';
import loadingOverlay from '../../../mixins/loading-overlay';
import url from "../../../mixins/url";
import api from "../../../mixins/api";
import helpers from "../../../mixins/helpers";
import Vue from "vue";
import NumberCard from "./../../SharedComponents/NumberCard";
import SwipingCard from "./../../SharedComponents/SwipingCard";
import Modals from "../../../plugins/Modals";
import OptionsModal from "../../OptionsModal.vue";
import BillingAddress from "./BillingAddress.vue";
import ShippingAddress from "./ShippingAddress.vue";

export default {
    mixins: [loadingOverlay, beep, url, api, helpers],

    components: {
        ShippingAddress,
        BillingAddress,
        OptionsModal,
        NumberCard,
        SwipingCard,

    },

    props: {
        data_collection_id: null,
    },

    data() {
        return {
            minShelfLocation: '',
            singleScanEnabled: true,
            addToRequested: false,
            scannedDataCollectionRecord: null,
            scannedProduct: null,
            dataCollection: null,
            dataCollectionRecords: [],
            nextUrl: null,
            page: 1,
            per_page: 50,
            csv: null,
            warehouses: [],
            buttonsEnabled: true,
            selectedInventoryId: null,
            manuallyExpandComments: false,
            input_comment: '',
            collectionTypes: {
                'App\\Models\\DataCollectionTransferIn': this.$t('Transfer In'),
                'App\\Models\\DataCollectionTransferOut': this.$t('Transfer Out'),
                'App\\Models\\DataCollectionStocktake': this.$t('Stocktake'),
            },
            selectedPrinter: null,
            selectedPaymentType: null,
            selectedBillingAddress: null,
            selectedShippingAddress: null,
            paymentTypeAlreadySelected: false,
            paymentAmount: 0,
            reloadDataCollectionTimer: null,
            newComment: '',
            commentInputVisible: false,
            commentInputRecordId: null,
        };
    },

    mounted() {
        if (!Vue.prototype.$currentUser['warehouse_id']) {
            this.$snotify.error(this.$t('You do not have warehouse assigned. Please contact administrator'), { timeout: 50000 });
            return;
        }

        this.selectedPrinter = this.getSelectedPrinter();

        window.onscroll = () => this.loadMoreWhenNeeded();

        Modals.EventBus.$on('hide::modal::set-transaction-printer-modal', (data) => {
            this.selectedPrinter = data.printer;
            if (typeof data.openTransactionStatusModal !== 'undefined' && data.openTransactionStatusModal) {
                this.$modal.showTransactionStatusModal();
            }
        });

        Modals.EventBus.$on('hide::modal::data-collection-choose-payment-type-modal', (data) => {
            if (data.paymentType) {
                this.selectedPaymentType = data.paymentType;
            }

            if ((typeof data.saveChanges !== 'undefined' && data.saveChanges) && this.selectedPaymentType) {
                this.$modal.showAddPaymentModal();
            }
        });

        Modals.EventBus.$on('show::modal::data-collection-add-payment-modal', this.onAddPaymentModalShown);

        Modals.EventBus.$on('hide::modal::data-collection-add-payment-modal', (data) => {
            if (data.amount) {
                this.paymentAmount = data.amount;
            }

            if ((typeof data.saveChanges !== 'undefined' && data.saveChanges) && this.paymentAmount) {
                this.setTransactionPayment();
            }
        });

        Modals.EventBus.$on('hide::modal::data-collection-record-update-quantity-modal', (data) => {
            if (
                typeof data.quantity !== 'undefined' &&
                typeof data.id !== 'undefined' &&
                typeof data.saveChanges !== 'undefined' &&
                data.saveChanges
            ) {
                this.updateCollectionRecordQuantity(data.id, data.quantity);
            }
            // TODO: recalculate after quantity is updated
        });

        Modals.EventBus.$on('hide::modal::data-collection-record-update-unit-price-modal', (data) => {
            if (
                typeof data.unitSoldPrice !== 'undefined' &&
                typeof data.id !== 'undefined' &&
                typeof data.saveChanges !== 'undefined' &&
                data.saveChanges
            ) {
                this.updateCollectionRecordUnitPrice(data.id, data.unitSoldPrice);
            }
        });

        Modals.EventBus.$on('hide::modal::find-address-modal', (data) => {
            if (data.billingAddress) {
                this.selectedBillingAddress = data.billingAddress;
            }

            if (data.shippingAddress) {
                this.selectedShippingAddress = data.shippingAddress;
            }

            if (
                (typeof data.saveChanges !== 'undefined' && data.saveChanges) &&
                (this.selectedShippingAddress || this.selectedBillingAddress) &&
                (this.selectedShippingAddress !== this.dataCollection['shipping_address_id'] || this.selectedBillingAddress !== this.dataCollection['billing_address_id'])
            ) {
                this.setTransactionCustomer();
            }
        });

        Modals.EventBus.$on('hide::modal::data-collection-transaction-status-modal', (data) => {
            if (typeof data.archiveTransaction !== 'undefined' && data.archiveTransaction) {
                this.archiveTransaction(true);
            }
        });

        this.getUrlFilterOrSet('warehouse_code', Vue.prototype.$currentUser['warehouse']['code']);

        this.loadWarehouses();

        this.reloadDataCollection();
    },

    shown() {
        this.reloadDataCollection();
    },

    methods: {
        toggleAddToRequested() {
            setTimeout(() => {
                this.hideBvModal('configuration-modal');
            }, 200)
        },

        reloadDataCollection() {
            this.loadDataCollectorDetails();
            this.loadDataCollectorRecords();
        },

        onQuantityRequestModalHidden() {
            this.setFocusElementById('barcode_input');
            this.reloadDataCollection();
        },

        onShownConfigurationModal() {
            this.setFocusElementById('stocktake-input', true, true);
            this.buttonsEnabled = true;
        },

        onBarcodeScanned: function (barcode) {
            if (barcode === '') {
                return;
            }

            if (this.dataCollection['deleted_at'] !== null) {
                this.notifyError(this.$t('This collection is already archived'));
                return;
            }

            if (this.singleScanEnabled) {
                this.addSinglePiece(barcode);
            } else {
                this.$modal.showDataCollectorQuantityRequestModal(this.dataCollection['id'], barcode, this.addToRequested ? 'quantity_requested' : 'quantity_scanned');
            }
        },

        addSinglePiece(barcode) {
            if (this.reloadDataCollectionTimer) {
                clearTimeout(this.reloadDataCollectionTimer);
            }

            let data = {
                'data_collection_id': this.dataCollection['id'],
                'sku_or_alias': barcode,
                'quantity_scanned': 1,
            };

            if (this.addToRequested) {
                data['quantity_requested'] = 1;
            } else {
                data['quantity_scanned'] = 1;
            }

            this.apiPostDataCollectorActionsAddProduct(data)
                .then(() => {
                    this.notifySuccess('1 x ' + barcode);
                    this.reloadDataCollection();
                    this.reloadDataCollectionTimer = setTimeout(() => {
                        this.reloadDataCollection();
                    }, 2000);
                })
                .catch((error) => {
                    this.displayApiCallError(error);
                });
        },

        toggleSingleScanMode() {
            setTimeout(() => {
                this.hideBvModal('configuration-modal');
            }, 200)
        },

        loadWarehouses: function () {
            this.apiGetWarehouses({ 'per_page': 999, 'sort': 'name' })
                .then(response => {
                    this.warehouses = response.data.data;
                });
        },

        loadDataCollectorDetails: function () {
            let params = {
                'filter[id]': this.data_collection_id,
                'filter[with_archived]': true,
                'include': 'comments,comments.user,shippingAddress,billingAddress,payments'
            }

            this.apiGetDataCollector(params)
                .then(response => {
                    this.dataCollection = response.data.data[0];
                    if (this.dataCollection.shipping_address_id) {
                        this.selectedShippingAddress = this.dataCollection.shipping_address_id;
                    }
                    if (this.dataCollection.billing_address_id) {
                        this.selectedBillingAddress = this.dataCollection.billing_address_id;
                    }
                    if (this.dataCollection.total_outstanding !== null && this.dataCollection.total_outstanding <= 0) {
                        this.$modal.showTransactionStatusModal();
                    }
                })
                .catch(error => {
                    console.error(error);
                    this.displayApiCallError(error);
                });
        },

        transferToWarehouseClick() {
            this.$bvModal.hide('configuration-modal');
            this.$bvModal.show('transferToModal');
        },

        transferToWarehouse(warehouse) {
            let data = {
                'action': 'transfer_to_scanned',
                'destination_warehouse_id': warehouse['id'],
            }

            this.apiUpdateDataCollection(this.data_collection_id, data)
                .then(() => {
                    this.$bvModal.hide('configuration-modal');
                    location.href = '/data-collector';
                    // setTimeout(() => {
                    //     this.reloadDataCollection();
                    // }, 500);
                })
                .catch(error => {
                    this.showException(error);
                });

            this.$bvModal.hide('transferToModal');
        },

        transferStockIn() {
            let data = {
                'action': 'transfer_in_scanned',
            }

            this.apiUpdateDataCollection(this.data_collection_id, data)
                .then(() => {
                    this.$snotify.success(this.$t('Stock transferred in successfully'));
                    this.$bvModal.hide('configuration-modal');
                    setTimeout(() => {
                        this.reloadDataCollection();
                    }, 500);
                })
                .catch(error => {
                    this.showException(error);
                });
        },

        archiveTransaction(updateNextTransactionNumber = false) {
            let data = {
                'custom_uuid': null,
                'deleted_at': new Date().toISOString(),
            };

            if (updateNextTransactionNumber) {
                data['action'] = 'update_next_transaction_number';
            }

            this.apiUpdateDataCollection(this.data_collection_id, data)
                .then(() => {
                    this.$snotify.success(this.$t('Transaction archived successfully'));
                    this.$emit('transactionFinished');
                    this.$bvModal.hide('configuration-modal');
                    setTimeout(() => {
                        this.reloadDataCollection();
                    }, 500);
                })
                .catch(error => {
                    this.showException(error);
                });
        },

        saveTransaction() {
            this.apiUpdateDataCollection(this.data_collection_id, {
                'name': '',
                'action': 'import_as_sale',
                'custom_uuid': '',
            })
            .then(() => {
                this.$snotify.success(this.$t('Transaction saved successfully'));
                this.$emit('transactionFinished');
                this.$bvModal.hide('configuration-modal');
            })
            .catch(error => {
                this.showException(error);
            });
        },

        selectPrinter() {
            this.$modal.showSetTransactionPrinterModal(this.selectedPrinter);
        },

        selectCustomer() {
            this.$modal.showFindAddressModal();
            this.$bvModal.hide('configuration-modal');
        },

        selectPayment() {
            this.$modal.showSetPaymentTypeModal(this.selectedPaymentType);
            this.$bvModal.hide('configuration-modal');
        },

        autoScanAll() {
            let data = {
                'action': 'auto_scan_all_requested',
            }

            this.apiUpdateDataCollection(this.data_collection_id, data)
                .then(() => {
                    this.$snotify.success(this.$t('Auto scan completed successfully'));
                    this.$bvModal.hide('configuration-modal');
                    setTimeout(() => {
                        this.reloadDataCollection();
                    }, 500);
                })
                .catch(error => {
                    this.showException(error);
                });
        },

        loadMoreWhenNeeded() {
            if (this.isLoading) {
                return;
            }

            if (this.isMoreThanPercentageScrolled(70) === false) {
                return;
            }

            if (this.nextUrl === null) {
                return;
            }

            // we double per_page every second page load to avoid hitting the API too hard
            // and we will limit it to 100-ish per_page
            if ((this.page % 2 === 0) && (this.per_page < 100)) {
                this.page = this.page / 2;
                this.per_page = this.per_page * 2;
            }

            this.loadDataCollectorRecords(++this.page);
        },

        loadDataCollectorRecords(page = 1) {
            this.showLoading();

            const params = this.$router.currentRoute.query;
            params['filter[data_collection_id]'] = this.data_collection_id;
            params['include'] = 'product,inventory,product.tags,product.aliases,prices,discount';
            params['per_page'] = this.per_page;
            params['page'] = page;

            this.apiGetDataCollectorRecords(params)
                .then((response) => {
                    if (page === 1) {
                        this.dataCollectionRecords = response.data.data;
                    } else {
                        this.dataCollectionRecords = this.dataCollectionRecords.concat(response.data.data);
                    }

                    this.page = response.data['meta']['current_page'];
                    this.nextUrl = response.data['links']['next'];
                })
                .catch((error) => {
                    this.displayApiCallError(error);
                })
                .finally(() => {
                    this.hideLoading();
                });
        },

        postCsvRecordsToApiAndCloseModal() {
            const data = this.csv.map(record => ({
                'product_sku': record.product_sku,
                'quantity_requested': record.quantity_requested,
                'quantity_scanned': record.quantity_scanned,
            }));

            //we removing header row from csv
            data.shift();

            const payload = {
                'data_collection_id': this.data_collection_id,
                'data': data,
            }

            this.apiPostCsvImport(payload)
                .then(() => {
                    this.notifySuccess(this.$t('Records imported'));
                    this.$bvModal.hide('configuration-modal');
                })
                .catch(e => {
                    this.displayApiCallError(e);
                })
                .finally(() => {
                    this.reloadDataCollection();
                });
        },

        downloadFileAndHideModal() {
            window.open(this.getDownloadLink, '_blank');

            this.hideBvModal('configuration-modal')
        },

        hideBvModal(ref) {
            this.$bvModal.hide(ref);
        },

        addComment() {
            let data = {
                "data_collection_id": this.dataCollection.id,
                "comment": this.input_comment
            };

            // quick hack to immediately display comment
            this.dataCollection.comments.unshift(data);

            this.apiPostDataCollectionComment(data)
                .then(() => {
                    this.loadDataCollectorDetails();
                    this.input_comment = '';
                    this.manuallyExpandComments = false;
                    this.setFocusElementById('barcode_input');

                })
                .catch((error) => {
                    console.error(error)
                    this.displayApiCallError(error);
                });
        },

        toggleExpandComments() {
            this.manuallyExpandComments = !this.manuallyExpandComments;
            if (this.manuallyExpandComments) {
                this.setFocusElementById('comment-input', true);
            } else {
                this.setFocusElementById('barcode_input', false);
            }
        },

        getSelectedPrinter() {
            const printer = localStorage.getItem('selectedTransactionsPrinter');

            if (printer) {
                return JSON.parse(printer);
            } else {
                return null;
            }
        },

        previewReceipt() {
            this.apiPreviewReceipt({ id: this.dataCollection.id })
                .then((response) => {
                    this.$modal.showPreviewTransactionReceiptModal(response.data);
                })
                .catch(error => {
                    this.displayApiCallError(error);
                })
        },

        printReceipt() {
            if (this.selectedPrinter === null) {
                this.$snotify.error(this.$t('Please select printer first'));
                return;
            }

            const data = {
                id: this.dataCollection.id,
                printer_id: this.selectedPrinter.id,
            };

            this.apiPrintTransactionReceipt(data)
                .then(() => {
                    this.notifySuccess(this.$t('Receipt sent to printer'));
                })
                .catch(error => {
                    this.displayApiCallError(error);
                })
        },

        emailPdfReceipt() {
            if (!this.selectedBillingAddress && !this.selectedShippingAddress) {
                this.$snotify.error(this.$t('Please select customer first'));
                return;
            }

            this.apiSendTransactionReceipt({ id: this.dataCollection.id })
                .then(() => {
                    this.notifySuccess(this.$t('Receipt has been sent to selected customer'));
                })
                .catch(error => {
                    this.displayApiCallError(error);
                })
        },

        setTransactionCustomer() {
            this.apiPutTransaction(this.dataCollection.id, {
                shipping_address_id: this.selectedShippingAddress ? this.selectedShippingAddress : this.selectedBillingAddress,
                billing_address_id: this.selectedBillingAddress ? this.selectedBillingAddress : this.selectedShippingAddress
            })
                .then(() => {
                    this.notifySuccess(this.$t('Customer selected'));
                    this.reloadDataCollection();
                })
                .catch(error => {
                    this.displayApiCallError(error);
                });
        },

        setTransactionPayment() {
            this.apiPostTransactionPayment({
                transaction_id: this.dataCollection.id,
                payment_type_id: this.selectedPaymentType.id,
                amount: this.paymentAmount
            })
                .then(() => {
                    this.notifySuccess(this.$t('Payment saved.'));
                    this.reloadDataCollection();
                })
                .catch(error => {
                    this.displayApiCallError(error);
                });
        },

        onAddPaymentModalShown() {
            this.setFocusElementById('transaction_payment_amount', true);
        },

        updateQuantity(record) {
            this.$modal.showUpdateDataCollectionRecordQuantityModal(record);
        },

        updateUnitPrice(record) {
            this.$modal.showUpdateDataCollectionRecordUnitPriceModal(record);
        },

        updateCollectionRecordQuantity(id, quantity) {
            this.apiUpdateDataCollectorRecord(id, {
                'quantity_scanned': quantity,
            })
                .then(() => {
                    this.notifySuccess(this.$t('Quantity updated'));
                    this.reloadDataCollection();
                })
                .catch(error => {
                    this.displayApiCallError(error);
                });
        },

        updateCollectionRecordUnitPrice(id, unitSoldPrice) {
            this.apiUpdateDataCollectorRecord(id, {
                'unit_sold_price': unitSoldPrice,
                'price_source': 'SET_BY_CASHIER',
                'price_source_id': null,
            })
                .then(() => {
                    this.notifySuccess('Unit price updated');
                    this.reloadDataCollection();
                })
                .catch(error => {
                    this.displayApiCallError(error);
                });
        },

        toggleCommentInput(record) {
            if (this.commentInputRecordId === record.id) {
                this.commentInputRecordId = null;
                this.newComment = '';
            } else {
                this.commentInputRecordId = record.id;
                if (record.comment) {
                    this.newComment = record.comment;
                } else {
                    this.newComment = '';
                }
                this.setFocusElementById(`comment-input-${record['id']}`, true);
            }
        },

        isCommentInputVisible(record) {
            return this.commentInputRecordId === record.id;
        },

        addCommentToRecord(record) {
            if (this.newComment.trim() === '') {
                return;
            }

            this.apiUpdateDataCollectorRecord(record.id, {
                'comment': this.newComment
            })
                .then(() => {
                    this.notifySuccess(this.$t('Comment added'));
                    this.reloadDataCollection();
                    this.commentInputRecordId = null;
                })
                .catch(error => {
                    this.displayApiCallError(error);
                });
        }
    },

    computed: {
        getDownloadLink() {
            let routeData = this.$router.resolve({
                path: this.$router.currentRoute.fullPath,
                query: {
                    'select': 'product_sku,product_name,total_transferred_in,total_transferred_out,quantity_requested,quantity_to_scan,quantity_scanned,inventory_quantity,product_price,product_sale_price,product_sale_price_start_date,product_sale_price_end_date,product_cost,last7days_sales,last14days_sales,last28days_sales',
                    'filter[data_collection_id]': this.data_collection_id,
                    filename: this.dataCollection['name'] + ".csv"
                }
            });

            return routeData.href;
        },

        commentsToShow() {
            return this.dataCollection.comments.length
                ? (this.manuallyExpandComments ? this.dataCollection.comments : [this.dataCollection.comments[0]])
                : [];
        }
    },
}
</script>


<style lang="scss">
.setting-list {
    width: 100%;
    color: #495057;
    display: flex;
    align-items: flex-start;
    margin-bottom: 5px;
}

.setting-list:hover,
.setting-list:focus {
    color: #495057;
    text-decoration: none;
    background-color: #f8f9fa;
}

.setting-icon {
    padding: 1rem;
    margin-right: 1rem;
    background-color: #f8f9fa;
    border-radius: 0.25rem;
}

.setting-icon:hover {
    background-color: unset;
}

.setting-title {
    color: #3490dc;
    font-weight: bolder;
    margin-bottom: 2px;
}

.setting-desc {
    color: #6c757d;
    font-size: 10pt;
}
</style>
