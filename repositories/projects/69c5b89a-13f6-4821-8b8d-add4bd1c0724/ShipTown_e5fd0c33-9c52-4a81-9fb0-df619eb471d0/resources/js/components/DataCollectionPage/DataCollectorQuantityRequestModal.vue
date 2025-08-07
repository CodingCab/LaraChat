<template>
    <div>
        <b-modal @ok="submitCount" size="md" id="data-collector-quantity-request-modal" scrollable no-fade hide-header hide-footer @shown="modalShown" @hidden="onHidden">
            <div class="row">
                <div class="col-lg-6" style="min-height: 85px;">
                    <product-info-card v-if="product" :product="product" :showProductDescriptions="false"></product-info-card>
                </div>
                <div class="col small">
                    <div class="row text-right mt-0 mb-3">
                        <div class="col">
                            <text-card label="price"
                                :text="prices && dataCollection ? prices[dataCollection['warehouse_code']]['current_price'] : 0"
                                :class="{ 'bg-warning': prices && dataCollection && (prices[dataCollection['warehouse_code']]['is_on_sale'] || prices[dataCollection['warehouse_code']]['current_price'] === 0) }"></text-card>
                            <number-card label="in stock"
                                :number="inventory && dataCollection ? inventory[dataCollection['warehouse_code']]['quantity'] : 0"
                                :class="{ 'bg-warning': inventory && dataCollectionRecord && dataCollectionRecord['quantity_requested'] >= inventory[dataCollection['warehouse_code']]['quantity'] }"></number-card>
                            <text-card label="shelf"
                                :text="inventory && dataCollection ? inventory[dataCollection['warehouse_code']]['shelf_location'] : 0"></text-card>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col text-right mt-3 mb-3 small">
                <number-card label="reserved"
                    :number="product && dataCollection ? product['inventory'][dataCollection['warehouse_code']]['quantity_reserved'] : 0"
                    :class="{ 'bg-warning': product && dataCollection ? product['inventory'][dataCollection['warehouse_code']]['quantity_reserved'] : 0 > 0 }"></number-card>
                <number-card label="requested"
                    :number="dataCollectionRecord ? dataCollectionRecord['quantity_requested'] : 0"></number-card>
                <number-card label="scanned"
                    :class="{ 'bg-warning': dataCollectionRecord && dataCollectionRecord['quantity_scanned'] > dataCollectionRecord['quantity_requested'] }"
                    :number="dataCollectionRecord ? dataCollectionRecord['quantity_scanned'] : 0"></number-card>
                <number-card label="to scan"
                    :class="{ 'bg-warning': dataCollectionRecord && dataCollectionRecord['quantity_scanned'] > 0 && dataCollectionRecord['quantity_to_scan'] > 0 }"
                    :number="dataCollectionRecord ? dataCollectionRecord['quantity_to_scan'] : 0"></number-card>
            </div>

            <div class="row-col">
                <div class="col-12 px-2">
                    <input class="form-control m-0"
                           style="font-size: 1.5em; text-align: right;"
                           :placeholder="placeholder"
                           :class="{ 'border-danger': this.quantity_to_add < 0, 'border-success': this.quantity_to_add > 0 }"
                           id="data-collection-record-quantity-request-input"
                           name="data-collection-record-quantity-request-input"
                           ref="data-collection-record-quantity-request-input"
                           dusk="data-collection-record-quantity-request-input"
                           v-model="quantity_to_add"
                           type="text"
                           pattern="[0-9]*"
                           inputmode="numeric"
                           @keyup.enter="submitCount" />
                </div>

                <!-- add here touchscreen numerical keyboard -->
                <div class="row mt-3 mx-1">
                    <div class="col-12">
                        <numpad
                            @press="appendNumber"
                            @backspace="backspace"
                            @ok="submitCount"
                            @cancel="closeModal"
                        />
                    </div>
                </div>
            </div>
        </b-modal>
    </div>
</template>

<script>
import loadingOverlay from '../../mixins/loading-overlay';
import BarcodeInputField from "../SharedComponents/BarcodeInputField";
import api from "../../mixins/api";
import helpers from "../../mixins/helpers";
import url from "../../mixins/url";
import Modals from "../../plugins/Modals";
import Numpad from "../SharedComponents/Numpad.vue";

export default {
    mixins: [loadingOverlay, url, api, helpers],

    components: {
        BarcodeInputField,
        Numpad,
    },

    props: {
        placeholder: {
            type: String,
            default: 'quantity',
        },
    },

    data: function () {
        return {
            data_collection_id: null,
            sku_or_alias: null,

            quantity_to_add: '',
            field_name: 'quantity_scanned',

            dataCollection: null,
            product: null,
            inventory: null,
            prices: null,
            dataCollectionRecord: null,
            productAliases: null,
        };
    },

    beforeMount() {
        Modals.EventBus.$on('show::modal::data-collector-quantity-request-modal', (data) => {
            this.data_collection_id = data['data_collection_id'];
            this.sku_or_alias = data['sku_or_alias'];
            this.field_name = data['field_name'];

            this.quantity_to_add = '';
            this.dataCollection = null;
            this.product = null;
            this.inventory = null;
            this.prices = null;
            this.dataCollectionRecord = null;

            this.reloadData();
        })
    },

    methods: {
        appendNumber(num) {
            if (num === '-') {
                if (this.quantity_to_add === '' || this.quantity_to_add === '-') {
                    this.quantity_to_add = '-';
                } else {
                    this.quantity_to_add = (Number(this.quantity_to_add) * -1).toString();
                }
            } else if (num === '.') {
                if (this.quantity_to_add === '' || this.quantity_to_add === '-') {
                    this.quantity_to_add = '0.';
                } else if (!this.quantity_to_add.includes('.')) {
                    this.quantity_to_add = this.quantity_to_add.toString() + '.';
                }
            } else if (this.quantity_to_add === '') {
                this.quantity_to_add = num.toString();
            } else {
                this.quantity_to_add = this.quantity_to_add.toString() + num.toString();
            }

            this.setFocusElementById('data-collection-record-quantity-request-input', false, false);
        },

        closeModal() {
            this.$bvModal.hide('data-collector-quantity-request-modal');
        },

        backspace() {
            this.quantity_to_add = this.quantity_to_add.toString().slice(0, -1);
            this.setFocusElementById('data-collection-record-quantity-request-input', false, false, 100);
        },

        onHidden() {
            this.$emit('hidden');
        },

        reloadData: function () {
            this.quantity_to_add = '';
            this.loadProduct();
            this.loadDataCollection();
            this.loadDataCollectionRecord();
        },

        modalShown() {
            this.setFocusElementById('data-collection-record-quantity-request-input', false, false, 100);
        },

        loadProduct: function () {
            this.apiGetProducts({
                'filter[sku_or_alias]': this.sku_or_alias,
                'include': 'inventory,tags,prices,aliases,inventory.warehouse,inventoryMovementsStatistics,inventoryTotals,productDescriptions,productPicture',
            })
                .then(response => {
                    if (response.data.data.length === 0) {
                        this.notifyError(this.$t('No product found with barcode ') + `"${this.sku_or_alias}"`);
                        return;
                    }
                    this.product = response.data.data[0];
                    this.inventory = this.product['inventory'];
                    this.prices = this.product['prices'];

                    const productAlias = this.product['aliases'].find(alias => alias['alias'] === this.sku_or_alias);
                    this.quantity_to_add = '';
                    // productAlias?.quantity

                    this.$bvModal.show('data-collector-quantity-request-modal');
                })
                .catch((error) => {
                    this.displayApiCallError(error);
                });
        },

        loadDataCollection: function () {
            this.apiGetDataCollector({
                'filter[id]': this.data_collection_id,
            })
                .then(response => {
                    if (response.data.data.length === 0) {
                        this.notifyError(this.$t('No collection found'));
                        return;
                    }
                    this.dataCollection = response.data.data[0];
                })
                .catch((error) => {
                    this.displayApiCallError(error);
                });
        },

        loadDataCollectionRecord() {
            this.apiGetDataCollectorRecords({
                'filter[data_collection_id]': this.data_collection_id,
                'filter[sku_or_alias]': this.sku_or_alias,
            })
                .then(response => {
                    if (response.data.data.length === 0) {
                        this.dataCollectionRecord = null;
                        return;
                    }
                    this.dataCollectionRecord = response.data.data[0];
                })
                .catch((error) => {
                    this.displayApiCallError(error);
                });
        },

        submitCount: function () {
            if (this.quantity_to_add === null) {
                return;
            }

            if (this.quantity_to_add === "") {
                return;
            }

            if (Math.abs(this.quantity_to_add) > 99999) {
                this.notifyError('Quantity is too large', { 'timeout': 3000 });
                this.setFocusElementById('data-collection-record-quantity-request-input', true);
                return;
            }

            let data = {
                'data_collection_id': this.dataCollection['id'],
                'sku_or_alias': this.sku_or_alias,
            };

            data[this.field_name] = this.quantity_to_add;

            this.apiPostDataCollectorActionsAddProduct(data)
                .then(() => {
                    this.$bvModal.hide('data-collector-quantity-request-modal');
                    this.notifySuccess(this.quantity_to_add + ' x ' + this.sku_or_alias);
                })
                .catch((error) => {
                    this.displayApiCallError(error);
                });

        }
    },
}
</script>

