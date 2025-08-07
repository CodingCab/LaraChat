<template>
    <div v-if="order">
        <div class="row" v-if="showMultipackerWarning">
            <div class="col">
                <div class="alert alert-danger" role="alert">
                    {{ $t('This order is already opened by someone else. Be careful') }}
                </div>
            </div>
        </div>

        <b-alert>Return Mode</b-alert>

        <div v-if="getUrlParameter('return_scan_mode', false)" class="row">
            <div class="col">
                <div class="alert alert-danger" role="alert">
                    {{ $t('Return Mode') }}
                </div>
            </div>
        </div>

        <div v-if="getUrlParameter('return_scan_mode', false) === false && order && order['is_packed']" class="row">
            <div class="col">
                <div class="alert alert-danger" role="alert">
                    {{ $t('Order already packed...') }}
                </div>
            </div>
        </div>

        <div v-if="!isLoading">
            <div class="row-col tabs-container mb-0 mt-1 small w-100">
                <ul class="nav nav-tabs small">
                    <li class="nav-item bg-white-active">
                        <a class="nav-link" :class="{active: currentTab === 'packlist'}" href="#" @click.prevent="currentTab='packlist'">Packlist</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" :class="{active: currentTab === 'orderDetails'}" href="#" @click.prevent="currentTab='orderDetails'">Details</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" :class="{active: currentTab === 'shippingAddress'}" href="#" @click.prevent="currentTab='shippingAddress'">Addresses</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" :class="{active: currentTab === 'orderActivities'}" href="#" @click.prevent="currentTab='orderActivities'">Activity</a>
                    </li>
                </ul>
            </div>

            <div class="row-col mb-2" v-if="currentTab === 'packlist'">
                <order-details :order="order" />
            </div>

            <template v-if="currentTab === 'packlist'">
                <search-and-option-bar-observer/>
                <search-and-option-bar :isStickable="true">
                    <barcode-input-field :input_id="'barcode-input'" @barcodeScanned="packBarcode"
                        :placeholder="$t('Enter sku or alias to ship 1 piece')" ref="barcode" />
                    <template v-slot:buttons>
                        <top-nav-button v-b-modal="'optionsModal'" />
                    </template>
                </search-and-option-bar>
            </template>

            <div v-if="currentTab === 'packlist'">

                <div v-show="manuallyExpandComments" class="row mx-1 my-2">
                    <input id="comment-input" ref="newCommentInput" v-model="input_comment" class="form-control"
                        :placeholder="$t('Add comment here')" @keypress.enter="addComment" />
                </div>

                <div class="my-1" v-if="commentsToShow.length">
                    <div class="d-flex mx-1" v-for="(comment, index) in commentsToShow" :key="`comment-${index}`" @click="toggleExpandComments">
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
            </div>

            <template v-if="orderProducts.length === 0 && currentTab === 'packlist'">
                <div class="row mb-3">
                    <div class="col">
                        <div class="alert alert-info" role="alert">
                            {{ $t('No products found') }}
                        </div>
                    </div>
                </div>
            </template>

            <template v-if="currentTab === 'packlist'">
                <div v-for="(group, key) in packlist" class="row mb-3" :key="`packlist-${key}`">
                    <div class="col">
                        <packlist-entry :picklistItem="group" :key="group.id" @swipeRight="shipAll" @swipeLeft="shipPartialSwiped" />
                    </div>
                </div>

                <div v-for="(group, key) in packed" class="row mb-3" :key="`packed-${key}`">
                    <div class="col">
                        <packed-entry :picklistItem="group" :key="group.id" @swipeLeft="shipPartialSwiped" />
                    </div>
                </div>
            </template>

            <div class="container" v-if="currentTab === 'orderDetails'">
                <div class="row">
                    <div class="col">
                        <column-wrapped>
                            <template #left-text>{{ $t('shipping method code') }}</template>
                            <template #right-text>{{ order['shipping_method_code'] }}</template>
                        </column-wrapped>
                        <column-wrapped :leftText="$t('shipping method name')" :rightText="order['shipping_method_name']"/>
                        <column-wrapped :leftText="$t('label template')" :rightText="order['label_template']"/>
                        <column-wrapped :leftText="$t('product lines')" :rightText="toNumberOrDash(order['order_products_totals']['count'])"/>
                        <column-wrapped :leftText="$t('quantity ordered')" :rightText="toNumberOrDash(order['order_products_totals']['quantity_ordered'])"/>
                        <column-wrapped :leftText="$t('total products')" :rightText="toNumberOrDash(order['total_products'])"/>
                        <column-wrapped :leftText="$t('total shipping')" :rightText="toNumberOrDash(order['total_shipping'])"/>
                        <column-wrapped :leftText="$t('total discounts')" :rightText="toNumberOrDash(order['total_discounts'])"/>
                        <column-wrapped :leftText="$t('total order')" :rightText="toNumberOrDash(order['total_order'])"/>
                        <column-wrapped :leftText="$t('total paid')" :rightText="toNumberOrDash(order['total_paid'])"/>

                        <column-wrapped :left-text="$t('payments')">
                            <template #right-text>
                                <div v-for="payment in order_payments" :key="payment.id">
                                    <b>{{ payment.name }} {{ toNumberOrDash(payment.amount) }}</b>
                                </div>
                            </template>
                        </column-wrapped>

                        <column-wrapped :leftText="$t('total outstanding')" :rightText="toNumberOrDash(order['total_outstanding'])"/>
                        <column-wrapped :leftText="$t('packed by')" :rightText="order['packer'] ? order['packer']['name'] : '&nbsp'"/>
                        <column-wrapped :leftText="$t('placed at')" :rightText="formatDateTime(order['order_placed_at'], 'MMM DD H:mm')"/>
                        <column-wrapped :leftText="$t('picked at')" :rightText="formatDateTime(order['picked_at'], 'MMM DD H:mm')"/>
                        <column-wrapped :leftText="$t('packed at')" :rightText="formatDateTime(order['packed_at'], 'MMM DD H:mm')"/>
                        <column-wrapped :leftText="$t('closed at')" :rightText="formatDateTime(order['order_closed_at'], 'MMM DD H:mm')"/>


                        <column-wrapped :left-text="$t('Shipping Numbers')" right-class="text-left">
                            <template #right-text>
                                <div v-for="shipment in order_shipments">
                                    <b>{{ formatDateTime(shipment['created_at']) }}</b>
                                    <a :href="shipment['tracking_url']" target="_blank">
                                        {{ shipment['shipping_number'] }}
                                    </a>
                                    {{ $t('by') }} {{ shipment['user'] ? shipment['user']['name'] : '' }}
                                    <a class="btn btn-link btn-sm small" :href="shippingContentUrl(shipment)" target="_blank">
                                        <font-awesome-icon icon="file-download" />
                                    </a>
                                </div>
                            </template>
                        </column-wrapped>
                    </div>
                </div>
            </div>

            <div class="container" v-if="currentTab === 'shippingAddress'">
                <div class="row">
                    <div class="col-12">
                        <div
                            class="row col d-flex font-weight-bold pb-1 text-uppercase small text-secondary align-content-center text-center justify-content-center">
                            {{ $t('SHIPPING ADDRESS') }}
                        </div>

                        <column-wrapped :left-text="$t('email')">
                            <template #right-text>
                                <a :href="'mailto:' + order['shipping_address']['email'] + '?subject=Order #' + order['order_number']">{{order['shipping_address']['email'] }} </a>
                            </template>
                        </column-wrapped>

                        <column-wrapped :left-text="$t('phone')">
                            <template #right-text>
                                <a :href="'tel:' + order['shipping_address']['phone']">{{order['shipping_address']['phone'] }}</a>
                            </template>
                        </column-wrapped>

                        <br>

                        <column-wrapped :left-text="$t('first_name')" :right-text="order['shipping_address']['first_name']"></column-wrapped>
                        <column-wrapped :left-text="$t('last_name')" :right-text="order['shipping_address']['last_name']"></column-wrapped>
                        <column-wrapped :left-text="$t('company')" :right-text="order['shipping_address']['company']"></column-wrapped>
                        <column-wrapped :left-text="$t('address1')" :right-text="order['shipping_address']['address1']"></column-wrapped>
                        <column-wrapped :left-text="$t('address2')" :right-text="order['shipping_address']['address2']"></column-wrapped>
                        <column-wrapped :left-text="$t('postcode')" :right-text="order['shipping_address']['postcode']"></column-wrapped>
                        <column-wrapped :left-text="$t('city')" :right-text="order['shipping_address']['city']"></column-wrapped>
                        <column-wrapped :left-text="$t('country_code')" :right-text="order['shipping_address']['country_code']"></column-wrapped>
                        <column-wrapped :left-text="$t('country_name')" :right-text="order['shipping_address']['country_name']"></column-wrapped>
                        <column-wrapped :left-text="$t('tax')" :right-text="order['shipping_address']['tax_id']"></column-wrapped>
                        <column-wrapped :left-text="$t('fax')" :right-text="order['shipping_address']['fax']"></column-wrapped>
                        <column-wrapped :left-text="$t('region')" :right-text="order['shipping_address']['region']"></column-wrapped>
                        <column-wrapped :left-text="$t('state_code')" :right-text="order['shipping_address']['state_code']"></column-wrapped>
                        <column-wrapped :left-text="$t('state_name')" :right-text="order['shipping_address']['state_name']"></column-wrapped>
                        <column-wrapped :left-text="$t('website')" :right-text="order['shipping_address']['website']"></column-wrapped>
                        <column-wrapped :left-text="$t('locker box code')" :right-text="order['shipping_address']['locker_box_code']"></column-wrapped>

                        <div v-if="order['billing_address']" class="mt-2">
                            <div class="row col d-block font-weight-bold pb-1 text-uppercase small text-secondary align-content-center text-center">
                                {{ $t('BILLING ADDRESS') }}
                            </div>

                            <column-wrapped :left-text="$t('email')">
                                <template #right-text>
                                    <a :href="'mailto:' + order['billing_address']['email'] + '?subject=Order #' + order['order_number']">{{order['billing_address']['email'] }} </a>
                                </template>
                            </column-wrapped>

                            <column-wrapped :left-text="$t('phone')">
                                <template #right-text>
                                    <a :href="'tel:' + order['billing_address']['phone']">{{order['billing_address']['phone'] }}</a>
                                </template>
                            </column-wrapped>
                            <column-wrapped :left-text="$t('first_name')" :right-text="order['billing_address']['first_name']"></column-wrapped>
                            <column-wrapped :left-text="$t('last_name')" :right-text="order['billing_address']['last_name']"></column-wrapped>
                            <column-wrapped :left-text="$t('company')" :right-text="order['billing_address']['company']"></column-wrapped>
                            <column-wrapped :left-text="$t('address1')" :right-text="order['billing_address']['address1']"></column-wrapped>
                            <column-wrapped :left-text="$t('address2')" :right-text="order['billing_address']['address2']"></column-wrapped>
                            <column-wrapped :left-text="$t('postcode')" :right-text="order['billing_address']['postcode']"></column-wrapped>
                            <column-wrapped :left-text="$t('city')" :right-text="order['billing_address']['city']"></column-wrapped>
                            <column-wrapped :left-text="$t('country_code')" :right-text="order['billing_address']['country_code']"></column-wrapped>
                            <column-wrapped :left-text="$t('country_name')" :right-text="order['billing_address']['country_name']"></column-wrapped>
                            <column-wrapped :left-text="$t('tax')" :right-text="order['billing_address']['tax_id']"></column-wrapped>
                            <column-wrapped :left-text="$t('fax')" :right-text="order['billing_address']['fax']"></column-wrapped>
                            <column-wrapped :left-text="$t('region')" :right-text="order['billing_address']['region']"></column-wrapped>
                            <column-wrapped :left-text="$t('state_code')" :right-text="order['billing_address']['state_code']"></column-wrapped>
                            <column-wrapped :left-text="$t('state_name')" :right-text="order['billing_address']['state_name']"></column-wrapped>
                            <column-wrapped :left-text="$t('website')" :right-text="order['billing_address']['website']"></column-wrapped>
                        </div>
                    </div>
                </div>
            </div>

            <template v-if="currentTab === 'orderActivities'">
                <div class="row small" v-for="activity in order_activities" :key="activity.id">
                    <span :title="formatDateTime(activity['created_at'], 'YYYY-MM-DD H:mm:ss')">
                        {{ formatDateTime(activity['created_at'], 'MMM DD H:mm') }}:
                    </span>
                    <span class="flex-nowrap ml-1">
                        {{ activity['causer'] === null ? 'AutoPilot' : activity['causer']['name'] }}
                    </span>
                    <span class="flex-nowrap ml-1">
                        {{ activity['description'] }}
                    </span>
                    <div class="col-12 pl-3 text-nowrap"
                        v-for="(value, name) in activity['properties']['attributes'] ? activity['properties']['attributes'] : activity['properties']">
                        {{ name }} = {{ value }}
                    </div>
                </div>

                <div class="mt-2 text-center font-weight-bold text-uppercase">
                    <a :href="activityLogsLink">{{ $t('See All') }}</a>
                </div>
            </template>
        </div>

        <b-modal ref="shippingNumberModal2" no-fade hide-footer hide-header dusk="shippingNumberModal"
            @shown="setFocusElementById('shipping_number_input')" @hidden="setFocusOnBarcodeInput()">
            <input id="shipping_number_input" class="form-control" :placeholder="$t('Scan shipping number')" v-model="shippingNumberInput"
                @keyup.enter.prevent="addShippingNumber" />
            <hr>
            <div class="text-right">
                <button type="button" @click.prevent="closeAskForShippingNumberModal" class="btn btn-secondary">{{ $t('Cancel') }}</button>
                <button type="button" @click.prevent="addShippingNumber" class="btn btn-primary">{{ $t('OK') }}</button>
            </div>
        </b-modal>

        <options-modal @hidden="reloadData">
            <div class="row mb-2">
                <div class="col">
                    <div class="setting-title">{{ $t('Automatically Print Courier Label') }}</div>
                    <div class="setting-desc">{{ $t('Courier label will be automatically printer when all products are shipped') }}</div>
                </div>
                <div class="custom-control custom-switch m-auto text-right align-content-center float-right w-auto">
                    <input type="checkbox" @change="toggleAutoPrintCourierLabel" class="custom-control-input" id="auto_print_courier_label" v-model="auto_print_courier_label">
                    <label class="custom-control-label" for="auto_print_courier_label"></label>
                </div>
            </div>

            <hr>

            <column-wrapped :leftText="$t('status')">
                <template #right-text>
                    <div class="form-group">
                        <select id="selectStatus" class="form-control" @change="changeStatus" v-model="order.status_code">
                            <option v-for="orderStatus in orderStatuses" :value="orderStatus.code" :key="orderStatus.id">{{ orderStatus.code }}
                            </option>
                        </select>
                    </div>
                </template>
            </column-wrapped>

            <column-wrapped :leftText="$t('courier')">
                <template #right-text>
                    <div class="form-group">
                        <select id="courierSelect" class="form-control" @change="updateLabelTemplate" v-model="order.label_template">
                            <option :value="''"></option>
                            <option v-for="shippingCourier in shippingCouriers" :value="shippingCourier.code" :key="shippingCourier.code">
                                {{ shippingCourier.code }}</option>
                        </select>
                    </div>
                </template>
            </column-wrapped>


            <modal-button @click.prevent="printExtraLabelClick()" :disabled="order.label_template === ''" >{{ $t('Print Courier Label') }}</modal-button>
            <modal-button @click.prevent="printShippingLabel('address_label')">{{ $t('Print Address Label') }}</modal-button>
            <br>
            <modal-button @click.prevent="showShippingNumberRequestModal">{{ $t('Add Shipping Number')}}</modal-button>
            <modal-button @click.prevent="openPreviousOrder" :disabled="previous_order_id === null">{{ $t('Open Previous Order')}}</modal-button>
        </options-modal>
    </div>
</template>

<script>
import beep from '../mixins/beep';
import loadingOverlay from '../mixins/loading-overlay';

import PacklistEntry from './Packlist/PacklistEntry';
import PackedEntry from './Packlist/PackedEntry';

import OrderDetails from "./Packlist/OrderDetails";
import BarcodeInputField from "./SharedComponents/BarcodeInputField";
import url from "../mixins/url";
import api from "../mixins/api";
import helpers from "../mixins/helpers";
import Vue from "vue";
import ColumnWrapped from "./Orders/ColumnWrapped.vue";
import OptionsModal from "./OptionsModal.vue";
import ModalButton from "./Reports/ModalButton.vue";

export default {
    mixins: [loadingOverlay, beep, url, api, helpers, BarcodeInputField],

    components: {
        ModalButton,
        OptionsModal,
        ColumnWrapped,
        PacklistEntry,
        BarcodeInputField,
        OrderDetails,
        PackedEntry,

    },

    props: {
        order_id: null,
        previous_order_id: null,
    },

    data: function () {
        return {
            auto_print_courier_label: true,
            return_scan_mode: false,
            order: null,
            orderProducts: [],
            orderStatuses: [],
            shippingCouriers: [],

            input_comment: '',
            shippingNumberInput: '',

            packlist: null,
            packed: [],
            groupedPacked: {},
            groupedPacklist: {},

            canClose: true,
            somethingHasBeenPackedDuringThisSession: false,
            autoLabelAlreadyPrinted: false,

            manuallyExpandComments: false,
            currentTab: 'packlist',
            order_activities: null,
        };
    },

    watch: {
        '$route' (to, from) {
            if (to.path === from.path) {
                this.$nextTick(() => {
                    this.reloadData();
                });
            }
        },

        order() {
            if (this.order === null) {
                return;
            }

            if (this.somethingHasBeenPackedDuringThisSession === false) {
                return;
            }

            if (this.order['order_products_totals']['quantity_to_ship'] > 0) {
                return;
            }

            if (this.order['is_packed'] === true) {
                return;
            }

            this.somethingHasBeenPackedDuringThisSession = false;

            this.completeOrder();
        },
    },


    mounted() {
        if (!Vue.prototype.$currentUser['warehouse_id']) {
            this.$snotify.error(this.$t('You do not have warehouse assigned. Please contact administrator'), { timeout: 50000 });
            return
        }
        this.auto_print_courier_label = this.getUrlParameter('auto_print_courier_label', true);

        this.setUrlParameter('warehouse_id', Vue.prototype.$currentUser['warehouse_id']);

        this.reloadData();

        this.loadOrderStatuses();
        this.loadShippingCouriers();

        this.reloadPageAfterInactivity();

        $('#shippingNumberModal2').modal();
    },

    methods: {
        reloadData() {
            this.return_scan_mode = this.getUrlParameter('return_scan_mode', false);

            this.loadOrder();
            this.loadOrderProducts();
            this.loadOrderActivities();
        },

        reloadPageAfterInactivity() {
            let time = new Date().getTime();

            const setActivityTime = (e) => {
                if (new Date().getTime() - time >= 60 * 1000 * 5) {
                    this.loadOrder();
                }

                time = new Date().getTime();
            }

            document.body.addEventListener("scroll", setActivityTime);
            document.body.addEventListener("focus", setActivityTime);
            document.body.addEventListener("mousemove", setActivityTime);
            document.body.addEventListener("keypress", setActivityTime);
        },

        addComment() {
            let data = {
                "order_id": this.order['id'],
                "comment": this.input_comment
            };

            // quick hack to immediately display comment
            this.order.order_comments.unshift(data);

            this.apiPostOrderComment(data)
                .then(() => {
                    this.loadOrder();
                    this.input_comment = '';
                    this.manuallyExpandComments = false;
                    this.setFocusElementById('barcode-input');

                })
                .catch((error) => {
                    // remove first comment if it was not saved
                    this.order.order_comments.shift();
                    console.log(error)
                    this.displayApiCallError(error);
                });
        },

        toggleExpandComments() {
            this.manuallyExpandComments = !this.manuallyExpandComments;
            this.setFocusElementById(this.manuallyExpandComments ? 'comment-input' : 'barcode-input', this.manuallyExpandComments);
        },

        async checkIfPacker() {
            if (this.order === null) {
                return;
            }

            this.apiGetActivityLog({
                'filter[subject_type]': 'App\\Models\\Order',
                'filter[subject_id]': this.order.id,
                'filter[description]': 'Packsheet opened',
                'sort': '-id',
                'per_page': 1
            })
                .then(({ data }) => {
                    const activity = data.data.pop();

                    if (activity['causer_id'] !== Vue.prototype.$currentUser['id']) {
                        this.order = null;
                        this.notifyError(this.$t('Someone else opened packsheet for this order'), {
                            timeout: 0,
                            buttons: [
                                {
                                    text: this.$t('OPEN PACKSHEET #') + this.order.order_number,
                                    action: (toast) => {
                                        window.location.href = '/orders?search=' + this.order.order_number;
                                    }
                                },
                            ],
                        })
                    }
                });


            setTimeout(() => { this.checkIfPacker(); }, 10000);
        },

        async completeOrder() {
            await this.markAsPacked();

            if (await this.autoPrintLabelIfNeeded() === false) {
                return;
            }

            if (Vue.prototype.$currentUser['ask_for_shipping_number'] === true) {
                this.showShippingNumberRequestModal();
                return;
            }

            if ((this.packlist.length === 0) && this.canClose) {
                this.$emit('orderCompleted')
            }
        },

        loadOrder() {
            this.canClose = true;

            let params = {
                'filter[order_id]': this.order_id,
                'include': 'order_products_totals,order_comments,order_comments.user,order_shipments,shipping_address,billing_address',
            };

            return this.apiGetOrders(params)
                .then(({ data }) => {
                    this.order = data.data.length > 0 ? data.data[0] : null;
                })
                .catch((error) => {
                    this.displayApiCallError(error);
                });
        },

        loadOrderProducts() {
            const params = {
                'filter[order_id]': this.order_id,
                'filter[warehouse_id]': this.getUrlParameter('warehouse_id'),
                'sort': this.getUrlParameter('sort', 'inventory_source_shelf_location,product.department,product.category,sku_ordered'),
                'include': 'product,product.aliases,product.modelTags',
                'per_page': 999,
            };

            this.apiGetOrderProducts(params)
                .then(({ data }) => {
                    this.orderProducts = data.data;

                    this.packlist = this.orderProducts.filter(orderProduct => Number(orderProduct['quantity_to_ship']) > 0);
                    this.packed = this.orderProducts.filter(orderProduct => Number(orderProduct['quantity_to_ship']) === 0 && Number(orderProduct['quantity_split']) === 0);
                })
                .catch((error) => {
                    this.displayApiCallError(error);
                });
        },

        loadOrderStatuses() {
            this.apiGetOrderStatus({
                'filter[hidden]': 0,
                'per_page': 999,
                'sort': 'code'
            })
            .then(({ data }) => {
                this.orderStatuses = data.data;
            })
        },

        loadShippingCouriers() {
            this.apiGetShippingServices({
                'per_page': 999,
                'sort': 'code'
            })
            .then(({ data }) => {
                this.shippingCouriers = data.data;
            })
        },

        loadOrderActivities() {
            let params = {
                'filter[subject_id]': this.order_id,
                'filter[subject_type]': 'App\\Models\\Order',
                'include': 'causer',
                'sort': '-id',
                'per_page': '999',
            };

            this.apiGetActivityLog(params)
                .then(({ data }) => {
                    this.order_activities = data.data;
                })
        },

        shipPartialSwiped(orderProduct) {
            this.$snotify.prompt('Partial shipment', {
                placeholder: this.$t('Enter quantity to ship:'),
                position: 'centerCenter',
                icon: false,
                buttons: [
                    {
                        text: 'OK',
                        action: (toast) => {

                            const maxQuantityAllowed = orderProduct['quantity_to_ship'];
                            const minQuantityAllowed = orderProduct['quantity_shipped'] * (-1);

                            if (
                                isNaN(toast.value)
                                || (toast.value > Number(maxQuantityAllowed))
                                || (toast.value < Number(minQuantityAllowed))
                            ) {
                                toast.valid = false;
                                return false;
                            }

                            this.$snotify.remove(toast.id);
                            this.shipOrderProduct(orderProduct, Number(toast.value));
                            this.setFocusElementById('barcode-input');
                        }
                    },
                    {
                        text: 'Cancel',
                        action: (toast) => {
                            this.$snotify.remove(toast.id);
                            this.setFocusElementById('barcode-input');
                        }
                    },
                ],
            });
        },

        changeStatus() {
            this.$bvModal.hide('optionsModal');

            this.apiUpdateOrder(this.order['id'], { 'status_code': this.order.status_code })
                .then(() => {
                    this.reloadData();
                    this.notifySuccess(this.$t('Status changed'))
                })
                .catch(() => {
                    this.apiActivitiesPost({
                        'subject_type': 'order',
                        'subject_id': this.order.id,
                        'description': 'Error when changing status'
                    });
                    this.notifyError(this.$t('Error when changing status'));
                });
        },

        showShippingNumberRequestModal() {
            this.$bvModal.hide('optionsModal');
            this.$refs.shippingNumberModal2.show();
        },

        closeAskForShippingNumberModal() {
            this.$refs.shippingNumberModal2.hide();
        },

        addShippingNumber() {
            if (this.shippingNumberInput === '') {
                return;
            }

            this.$refs.shippingNumberModal2.hide();

            let data = {
                'order_id': this.order_id,
                'shipping_number': this.shippingNumberInput,
            };

            this.apiPostOrderShipment(data)
                .then(() => {
                    if (this.packlist.length === 0) {
                        this.$emit('orderCompleted')
                    }

                    this.notifySuccess(this.$t('Shipping number saved'));
                })
                .catch(() => {
                    this.apiActivitiesPost({
                        'subject_type': 'order',
                        'subject_id': this.order.id,
                        'description': 'Error saving shipping number, try again'
                    });
                    this.notifyError(this.$t('Error saving shipping number, try again'));
                })
        },

        async markAsPacked() {
            if (this.order['is_packed'] === true) {
                return;
            }

            this.order['is_packed'] = true;
            this.order['packer_user_id'] = Vue.prototype.$currentUser['id'];

            return await this.apiUpdateOrder(this.order_id, {
                'is_packed': true,
                'packer_user_id': Vue.prototype.$currentUser['id']
            })
                .catch((error) => {
                    this.apiActivitiesPost({
                        'subject_type': 'order',
                        'subject_id': this.order_id,
                        'description': 'Error occurred when marking order as packed'
                    });
                    this.notifyError('Error: ' + error.response.message);
                });
        },

        shipOrderProduct(orderProduct, quantity) {
            orderProduct.quantity_shipped += quantity;
            orderProduct.quantity_to_ship -= quantity;

            if (orderProduct.quantity_to_ship <= 0) {
                let index = this.packlist.findIndex((item) => item.id === orderProduct.id);
                if (index !== -1) {
                    this.packlist.splice(index, 1);
                }
            }

            this.apiPostOrderProductShipment({
                'sku_shipped': orderProduct.sku_ordered,
                'product_id': orderProduct.product_id,
                'order_id': orderProduct.order_id,
                'order_product_id': orderProduct.id,
                'quantity_shipped': quantity,
            })
                .then((data) => {
                    this.somethingHasBeenPackedDuringThisSession = true;
                    this.notifySuccess(data.data.data.quantity_shipped + ' x ' + '' + ' shipped');
                })
                .catch((error) => {
                    this.apiActivitiesPost({
                        'subject_type': 'order',
                        'subject_id': this.order_id,
                        'description': 'Error occurred when shipping products, try again'
                    });
                    this.displayApiCallError(error);
                })
                .finally(() => {
                    this.reloadData();
                });
        },

        shipAll(orderProduct) {
            this.shipOrderProduct(orderProduct, orderProduct['quantity_to_ship']);
            this.setFocusElementById('barcode-input');
        },

        findEntry(barcode, array = null) {
            if (barcode === '') {
                return null;
            }

            let list = array || this.packlist;

            for (let element of list) {

                if (element.sku_ordered.toUpperCase() === barcode.toUpperCase()) {
                    return element;
                }

                if (typeof element.product === 'undefined') {
                    continue;
                }

                if (element.product === null) {
                    continue;
                }

                if (element.product.sku.toUpperCase() === barcode.toUpperCase()) {
                    return element;
                }

                if (typeof element.product.aliases === 'undefined') {
                    continue;
                }

                for (let alias of element.product.aliases) {
                    if (alias.alias.toUpperCase() === barcode.toUpperCase()) {
                        return element;
                    }
                }
            }

            return null;
        },

        packBarcode(barcode) {
            if (this.return_scan_mode) {
                this.returnBarcode(barcode, -1);
            } else {
                this.shipBarcode(barcode, 1);
            }

            this.setFocusElementById('barcode-input');
        },

        shipBarcode(barcode, quantity) {
            let pickItem = this.findEntry(barcode);

            if (!pickItem) {
                this.notifyError(this.$t('{barcode} not found on packlist!', { barcode }));
                return;
            }

            this.shipOrderProduct(pickItem, quantity);

            this.setFocusElementById('barcode-input');
        },

        returnBarcode(barcode, quantity) {
            let orderProduct = this.findEntry(barcode, this.orderProducts);

            if (!orderProduct) {
                this.notifyError(this.$t('{barcode} not found on packlist!', { barcode }));
                return;
            }

            this.shipOrderProduct(orderProduct, quantity);

            this.setFocusElementById('barcode-input');
        },

        getAddressLabelTemplateName() {
            if (this.getUrlParameter('address_label_template')) {
                return this.getUrlParameter('address_label_template');
            }

            if (this.order.label_template) {
                return this.order.label_template;
            }

            if (Vue.prototype.$currentUser.address_label_template) {
                return Vue.prototype.$currentUser.address_label_template;
            }

            return '';
        },

        async autoPrintLabelIfNeeded() {
            const autoPrint = this.getUrlParameter('auto_print_courier_label', 'true');
            if (autoPrint === false || autoPrint === 'false') {
                return false;
            }

            let template = this.getAddressLabelTemplateName();

            if (template === '') {
                this.notifyError(this.$t('No shipping label template selected, click on "Options" to select one'));
                return false;
            }

            if (this.autoLabelAlreadyPrinted) {
                return false;
            }

            this.autoLabelAlreadyPrinted = true;

            if (template) {
                return await this.printShippingLabel(template);
            }
        },

        printExtraLabelClick() {
            this.$bvModal.hide('optionsModal');
            this.setFocusElementById('barcode-input');

            this.printShippingLabel();
        },

        async printShippingLabel(shipping_service_code = null) {
            if (shipping_service_code === null) {
                shipping_service_code = this.getAddressLabelTemplateName();
            }

            let params = {
                'shipping_service_code': shipping_service_code,
                'order_id': this.order_id
            };

            return this.apiPostShippingLabel(params)
                .then((data) => {
                    this.notifySuccess(this.$t('Label generated'), false, {
                        closeOnClick: true,
                        timeout: 1,
                        buttons: []
                    });
                })
                .catch((error) => {
                    this.canClose = false;
                    let errorMsg = 'Error ' + error.response.status + ': ' + error.response.data.message;

                    this.notifyError(errorMsg, {
                        closeOnClick: true,
                        timeout: 0,
                        buttons: [
                            { text: 'OK', action: null },
                        ]
                    });

                    this.apiActivitiesPost({
                        'subject_type': 'order',
                        'subject_id': this.order.id,
                        'description': 'Error when posting shipping label request'
                    });
                })
                .finally(() => {
                    this.reloadData();
                });
        },

        openPreviousOrder() {
            this.$bvModal.hide('optionsModal');
            this.setFocusElementById('barcode-input');

            if (!this.previous_order_id) {
                this.notifyError('Not Available');
                return;
            }

            this.loadOrder(this.previous_order_id);
        },

        updateLabelTemplate() {
            this.$bvModal.hide('optionsModal');

            this.apiUpdateOrder(this.order_id, {
                'label_template': this.order.label_template
            })
                .then(() => {
                    this.reloadData();
                })
                .catch((error) => {
                    this.displayApiCallError(error);
                });
        },

        toggleAutoPrintCourierLabel() {
            this.$bvModal.hide('optionsModal');

            this.setUrlParameter('auto_print_courier_label', this.auto_print_courier_label);
        }
    },

    computed: {
        activityLogsLink() {
            return `/reports/activity-log?sort=-id&filter[subject_type]=App\\Models\\Order&filter[subject_id]=${this.order.id}`
        },
        showMultipackerWarning() {
            if (this.order === null) {
                return false;
            }

            return (this.order['packer_user_id'] && this.order['packer_user_id'] !== Vue.prototype.$currentUser['id']);
        },

        commentsToShow() {
            return this.order.order_comments.length
                ? (this.manuallyExpandComments ? this.order.order_comments : [this.order.order_comments[0]])
                : [];
        }
    },
}
</script>

<style scoped>
.tabs-container .nav-tabs {
    min-height: 28px;
    height: 28px;
    padding-top: 5px;
    padding-bottom: 0;
}

.tabs-container .nav-link {
    min-height: 24px;
    height: 24px;
    padding-top: 6px;
    padding-bottom: 2px;
    line-height: 1.1;
}

.tabs-container .nav-item {
    margin-bottom: 0;
}

.bg-white-active .nav-link.active {
    background-color: #fff !important;
    color: #212529 !important;
    border-color: #dee2e6 #dee2e6 #fff !important;
}
</style>
