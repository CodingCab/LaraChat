<template>
    <div class="card ml-0 pl-0 mb-3">
        <div class="grid-col-12 px-2 py-2 m-0 gap-none">
            <div class="col-span-12 sm:col-span-6 md:col-span-4 lg:col-span-3">
                <product-info-card :product="record['product']" :showProductDescriptions="false"/>
            </div>
            <div class="col-span-12 sm:col-span-6 md:col-span-4 lg:col-span-4">
                <div class="small col-lg-9 m-auto">
                    <hr class="my-2 d-lg-none">
                    <column-wrapped :left-text="$t('fulfilment center')" :right-text="record['fulfilment_center']"></column-wrapped>
                    <column-wrapped :left-text="$t('fc quantity available')">{{ toNumberOrDash(record['fc_quantity_available']) }}</column-wrapped>
                    <column-wrapped :left-text="$t('fc quantity incoming')">{{ toNumberOrDash(record['fc_quantity_incoming']) }}</column-wrapped>
                    <hr class="m-1">
                    <column-wrapped :left-text="$t('reorder point')">{{ toNumberOrDash(record['reorder_point']) }}</column-wrapped>
                    <column-wrapped :left-text="$t('restock level')">{{ toNumberOrDash(record['restock_level']) }}</column-wrapped>
                    <column-wrapped :left-text="$t('last sold at')"><span @click.prevent="showInventoryMovementsModal" class="text-primary cursor-pointer">{{ formatDateTime(record['last_sold_at']) }}</span></column-wrapped>
                    <column-wrapped :left-text="$t('last received at')"><span @click.prevent="showInventoryMovementsModal" class="text-primary cursor-pointer">{{ formatDateTime(record['first_received_at']) }}</span></column-wrapped>
                    <column-wrapped :left-text="$t('last counted at')"><span @click.prevent="showInventoryMovementsModal" class="text-primary cursor-pointer">{{ formatDateTime(record['last_counted_at']) }}</span></column-wrapped>
                    <column-wrapped :left-text="$t('sale price')">{{ record['sale_price'] }} ({{ formatDateTime(record['sale_start_date'], 'D MMM Y') }} - {{ formatDateTime(record['sale_end_date'], 'D MMM Y') }})</column-wrapped>
                    <hr class="my-2 d-lg-none">
                </div>
            </div>
            <div class="text-lg-center sd-none lg:sd-block lg:col-span-1">
                <text-card :label="$t('warehouse')" :text="record['warehouse_code']" class="text-center"></text-card>
            </div>
            <div class="col-span-12 md:col-span-4 lg:col-span-4">
                <div class="row-col text-right" @click="expanded = !expanded">
                    <div class="row-col text-right">
                        <text-card :label="$t('price')" :text="record['price']" :class="{ 'text-secondary': isOnSale }" ></text-card>
                        <text-card :label="$t('sale price')" v-if="isOnSale"  :text="record['sale_price']" class="bg-warning"></text-card>
                        <text-card label="" v-else text=""></text-card>
                        <number-card :label="$t('in stock')" :number="record['quantity_in_stock']" v-bind:class="{'bg-warning' : record['quantity_in_stock'] < 0 }"></number-card>
                        <number-card :label="$t('required')" v-if="Number(record['fc_quantity_available']) > 0" :number="record['quantity_required']"></number-card>
                        <text-card v-else text="N/A" class="fa-pull-right" :label="$t('required')"></text-card>
                    </div>
                    <div class="row-col text-right">
                        <number-card :label="$t('weeks cover')" :number="weeksCover"></number-card>
                        <text-card label="" text=""></text-card>
                        <number-card :label="$t('sold 7 days')" :number="record['quantity_sold_last_7_days']"></number-card>
                    <number-card :label="$t('incoming')" :number="record['quantity_incoming']"></number-card>
                    </div>
                </div>

                <div @click="expanded = !expanded" class="text-center text-secondary">
                    <font-awesome-icon v-if="expanded" icon="chevron-up" class="fa fa-xs"></font-awesome-icon>
                    <font-awesome-icon v-else icon="chevron-down" class="fa fa-xs"></font-awesome-icon>
                </div>

                <div v-if="expanded">
                    <template v-if="currentUser()['warehouse'] && record['warehouse_code'] === currentUser()['warehouse_code']">
                        <div class="row-col text-center mt-3 small text-secondary">
                            {{ $t('reorder point') }}
                        </div>

                        <div class="row-col text-nowrap">
                            <div class="input-group mb-3">
                                <button tabindex="-1" @click="minusReorderPoint" class="btn btn-danger mr-3" type="button" id="button-addon5" style="min-width: 45px">-</button>
                                <input tabindex="0"
                                       @keyup="onUpdateReorderPointEvent"
                                       v-model="newReorderPointValue"
                                       @focus="simulateSelectAll"
                                       type="number"
                                       inputmode="numeric"
                                       class="form-control text-center"
                                       style="font-size: 24px"
                                >
                                <button tabindex="-1" @click="plusReorderPoint" class="btn btn-success ml-3" type="button" id="button-addon6" style="min-width: 45px">+</button>
                            </div>
                        </div>

                        <div class="row-col text-center mt-3 small text-secondary">
                            {{ $t('restock level') }}
                        </div>
                        <div class="row-col text-nowrap">
                            <div class="input-group mb-3">
                                <button tabindex="-1" @click="minusRestockLevel" class="btn btn-danger mr-3" type="button" id="button-addon3" style="min-width: 45px">-</button>
                                <input tabindex="0"
                                       @keyup="onUpdateRestockLevelEvent"
                                       v-model="newRestockLevelValue"
                                       @focus="simulateSelectAll"
                                       type="number"
                                       inputmode="numeric"
                                       class="form-control text-center"
                                       v-bind:class="{ 'alert-danger': newRestockLevelValue < newReorderPointValue }"
                                       style="font-size: 24px"
                                >
                                <button tabindex="-1" @click="plusRestockLevel" class="btn btn-success ml-3" type="button" id="button-addon4" style="min-width: 45px">+</button>
                            </div>
                        </div>
                    </template>
                    <template v-else>
                        <column-wrapped :left-text="$t('reorder point')">
                            <template #right-text>
                                {{ toNumberOrDash(record['reorder_point']) }}
                            </template>
                        </column-wrapped>
                        <column-wrapped :left-text="$t('restock level')">
                            <template #right-text>
                                {{ toNumberOrDash(record['restock_level']) }}
                            </template>
                        </column-wrapped>
                    </template>
                    <div class="small" @click="expanded = !expanded" v-if="expanded">
                        <column-wrapped :left-text="$t('last movement at')">
                            <template #right-text>
                                <span @click.prevent="showInventoryMovementsModal" class="text-primary cursor-pointer">{{ formatDateTime(record['last_movement_at']) }}</span>
                            </template>
                        </column-wrapped>
                        <column-wrapped :left-text="$t('first received at')">
                            <template #right-text>
                                <span @click.prevent="showInventoryMovementsModal" class="text-primary cursor-pointer">{{ formatDateTime(record['first_received_at']) }}</span>
                            </template>
                        </column-wrapped>
                    </div>

                    <div class="mt-3 row-col text-center align-bottom pb-2 m-0 font-weight-bold text-uppercase small text-secondary">
                        {{ $t('Incoming') }}
                    </div>

                    <div v-for="dataCollectionRecord in dataCollectorRecords" :key="dataCollectionRecord['id']">
                        <div class="row col">
                            <div class="text-primary">
                                <a :href="'/data-collector/' + dataCollectionRecord['data_collection']['id']">
                                    {{ dataCollectionRecord['data_collection']['name'] }}
                                </a>
                            </div>
                        </div>
                        <div class="row col">
                            <div class="flex-fill">
                                <a class="text-secondary small" :href="'/data-collector/' + dataCollectionRecord['data_collection']['id']">
                                    {{ formatDateTime(dataCollectionRecord['data_collection']['created_at']) }}
                                </a>
                            </div>
                            <div class="">
                                <number-card :label="$t('requested')" :number="dataCollectionRecord['quantity_requested']"></number-card>
                                <number-card :label="$t('outstanding')" :number="dataCollectionRecord['quantity_requested'] - dataCollectionRecord['total_transferred_in']"></number-card>
                            </div>
                        </div>
                        <hr />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import loadingOverlay from '../../mixins/loading-overlay';
import helpers from "../../mixins/helpers";
import api from "../../mixins/api";
import url from "../../mixins/url";
import ProductCard from "../Products/ProductCard";
import BarcodeInputField from "../SharedComponents/BarcodeInputField";
import moment from "moment";
import ColumnWrapped from "../Orders/ColumnWrapped.vue";

export default {
        name: "RestockingRecordCard",
        mixins: [loadingOverlay, url, api, helpers],

        components: {
            ColumnWrapped,
            ProductCard,
            BarcodeInputField,
        },

        props: {
            record: null,
        },

        watch: {
            expanded: function (newValue, oldValue) {
                if (newValue) {
                    let params = {
                        'filter[product_id]': this.record['product_id'],
                        'filter[warehouse_id]': this.record['warehouse_id'],
                        'per_page': 10,
                        'include': 'dataCollection'
                    };

                    this.apiGetDataCollectorRecords(params)
                        .then((response) => {
                            this.dataCollectorRecords = response.data.data;
                        });
                }
            }
        },

        data: function() {
            return {
                expanded: false,
                dataCollectorRecords: [],
            };
        },

        computed: {
            weeksCover: {
                get: function() {
                    if (this.record['quantity_sold_last_7_days'] === null) {
                        return 0;
                    }

                    if (this.record['quantity_sold_last_7_days'] === 0) {
                        return 0;
                    }

                    if (this.record['quantity_in_stock'] <= 0) {
                        return 0;
                    }

                    return Math.floor(this.record['quantity_in_stock'] / (this.record['quantity_sold_last_7_days']));
                },
            },

            pricing: {
                get: function() {
                    let warehouseCode = this.record['warehouse_code'];

                    if (warehouseCode) {
                        return this.record['product']['prices'][warehouseCode];
                    }

                    return this.record['product']['prices'][0];
                },
            },
            isOnSale: {
                get: function() {
                    const salePriceIsCorrect = this.record['sale_price'] !== null && this.record['sale_price'] < this.record['price'];
                    const startDateInPast = moment(this.record['sale_start_date']).isSameOrBefore(moment());
                    const endDateInFuture = moment(this.record['sale_end_date']).isSameOrAfter(moment().subtract(1, 'day'));

                    return salePriceIsCorrect && startDateInPast && endDateInFuture;
                },
            },

            isSaleComing: {
                get: function () {
                    const startDateInFuture = moment(this.record['sale_start_date']).isAfter(moment());
                    const startDateWithin7Days = moment(this.record['sale_start_date']).isBefore(moment().add(7, 'days'));

                    return startDateInFuture && startDateWithin7Days;
                },
            },

            newRestockLevelValue: {
                get: function() {
                    return Number(this.record['restock_level']);
                },
                set: function(newValue) {
                    this.record['restock_level'] = Number(newValue);
                }
            },

            newReorderPointValue: {
                get: function() {
                    return Number(this.record['reorder_point']);
                },
                set: function(newValue) {
                    this.record['reorder_point'] = Number(newValue);
                }
            },
        },

        methods: {
            toggleExpanded() {
                this.expanded = !this.expanded;
            },

            minusRestockLevel() {
                if (Number(this.newRestockLevelValue) - 1 < Number(this.newReorderPointValue)) {
                    this.updateRestockLevel(Number(this.record['reorder_point']));
                    return;
                }

                this.updateRestockLevel(Number(this.record['restock_level']) - 1);
            },

            plusRestockLevel() {
                if (Number(this.newRestockLevelValue) < Number(this.newReorderPointValue)) {
                    this.updateRestockLevel(Number(this.record['reorder_point']));
                    return;
                }

                this.updateRestockLevel(Number(this.record['restock_level']) + 1);
            },

            minusReorderPoint() {
                if (Number(this.record['reorder_point']) === 0) {
                    this.updateReorderPoint(Math.ceil(Number(this.record['quantity_in_stock']) / 3));
                    return;
                }

                this.updateReorderPoint(Number(this.record['reorder_point']) - 1);
            },

            plusReorderPoint() {
                this.updateReorderPoint(Number(this.record['reorder_point']) + 1);
            },

            onUpdateRestockLevelEvent(keyboard_event) {
                this.updateRestockLevel(keyboard_event.target.value);
            },

            onUpdateReorderPointEvent(keyboard_event) {
                this.updateReorderPoint(keyboard_event.target.value);
            },

            postInventoryUpdate() {
                const originalQuantityRequired = Number(this.record['quantity_required']);
                const originalRestockLevel = Number(this.record['restock_level']);
                const originalReorderPoint = Number(this.record['reorder_point']);

                this.apiInventoryPost({
                        'id': this.record['inventory_id'],
                        'restock_level': this.record['restock_level'],
                        'reorder_point': this.record['reorder_point'],
                    })
                    .then(response => {
                        this.record['quantity_required'] = response.data.data[0]['quantity_required'];
                    })
                    .catch(error => {
                        this.record['quantity_required'] = originalQuantityRequired;
                        this.record['restock_level'] = originalRestockLevel;
                        this.record['reorder_point'] = originalReorderPoint;
                        this.notifyError(error);
                    });
            },

            updateRestockLevel(value) {
                if (this.record['reorder_point'] > value) {
                    return;
                }

                this.record['restock_level'] = value;

                this.postInventoryUpdate();
            },

            updateReorderPoint(value) {
                let newValue = Math.max(0, Number(value));

                if (this.record['reorder_point'] > 0) {
                    let ratio = this.record['restock_level'] / this.record['reorder_point'];

                    if (ratio < 1) {
                        ratio = 3;
                    }

                    this.record['restock_level'] = Math.ceil(newValue * ratio);
                }

                this.record['reorder_point'] = newValue;

                this.postInventoryUpdate();
            },

            showInventoryMovementsModal() {
                this.$emit('showModalMovement', this.record['inventory_id'])
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
