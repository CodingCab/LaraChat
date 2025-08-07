<template>

    <div class="swiper-container">
        <div class="swiper-wrapper">
            <div class="swiper-slide p-1">
                <div class="card">
                    <div class="row p-2 pl-2 rounded">

                        <div class="col-lg-5">
                            <h5 class="text-primary">
                                <font-awesome-icon icon="copy" class="fa-xs" role="button"
                                    @click="copyToClipBoard(order['order_number'])"></font-awesome-icon>
                                <a :href="'/orders?search=' + order['order_number']">{{ order['order_number'] }}</a>
                            </h5>

                            <div>
                                <div class="small font-weight-bold">{{ order['status_code'] }}</div>
                                <div class="small">{{ order['label_template'] }}</div>

                                <div class="small" v-if="order['order_shipments'] && order['order_shipments'].length > 0">{{ $t('Shipping Numbers') }}:
                                    <template v-for="shipment in order['order_shipments']">
                                        <a :href="shipment['tracking_url']" target="_blank" class="text-wrap mr-1">
                                            {{ shipment['shipping_number'] }}
                                        </a>
                                    </template>
                                </div>

                                <a v-for="tag in order.tags" class="badge text-uppercase" :key="tag.id" :href="'orders?has_tags=' + tag.name">
                                    {{ tag.name }}
                                </a>
                                <a v-if="!order.tags?.length" class="badge btn btn-outline-primary" @click="openTagModal" :dusk="`edit-tags-order-${order.id}`">
                                    {{ $t('Add Tags') }}
                                </a>
                                <a v-else @click="openTagModal" :dusk="`edit-tags-order-${order.id}`">
                                    <font-awesome-icon icon="edit" class="fa-sm cursor-pointer btn-outline-primary"></font-awesome-icon>
                                </a>
                            </div>
                        </div>

                        <div class="col-lg-7 align-text-top mt-1">
                            <div v-if="order['order_products_totals']" class="col text-center" @click="toggleOrderDetails">
                                <div class="d-flex flex-nowrap justify-content-between">
                                    <div>
                                        <number-card :label="$t('age')" :number="order['age_in_days']" :min-width="'50px'"></number-card>
                                    </div>
                                    <div>
                                        <number-card  :label="$t('lines')" :number="order['order_products_totals']['count']"
                                            :min-width="'50px'"></number-card>
                                    </div>
                                    <div class="d-none d-md-block" v-bind:class="{ 'bg-warning': order['total_paid'] < 0.01 }">
                                        <div class="text-center w-100 text-secondary small px-2">
                                            <small>{{ $t('total paid') }}</small>
                                        </div>
                                        <h4 class="text-center">
                                            {{ financial(order.total_paid).split('.')[0] }}<!--
                                                --><span style="font-size: 8pt">.{{ financial(order.total_paid).split('.')[1] }}</span>
                                        </h4>
                                    </div>
                                    <div class="d-none d-sm-block">
                                        <number-card :label="$t('ordered')" :number="order['order_products_totals']['quantity_ordered']"
                                            :min-width="'50px'"></number-card>
                                    </div>
                                    <div class="bg-warning" v-if="Number(order['order_products_totals']['quantity_split']) > 0">
                                        <number-card :label="$t('split')" :number="order['order_products_totals']['quantity_split']"
                                            :min-width="'50px'"></number-card>
                                    </div>
                                    <div>
                                        <number-card :label="$t('picked')" :number="order['order_products_totals']['quantity_picked']"
                                            :min-width="'50px'"></number-card>
                                    </div>
                                    <div class="bg-warning" v-if="Number(order['order_products_totals']['quantity_skipped_picking']) > 0">
                                        <number-card :label="$t('skipped')" :number="order['order_products_totals']['quantity_skipped_picking']"
                                            :min-width="'50px'"></number-card>
                                    </div>
                                    <div class="d-none d-md-block">
                                        <number-card :label="$t('shipped')" :number="order['order_products_totals']['quantity_shipped']"
                                            :min-width="'50px'"></number-card>
                                    </div>
                                    <div>
                                        <number-card :label="$t('to ship')" :number="order['order_products_totals']['quantity_to_ship']"
                                            :min-width="'50px'"></number-card>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 small" v-if="order['order_comments'].length > 0 && orderDetailsVisible === false">
                                <b>{{
                                    order['order_comments'][0]['user'] ? order['order_comments'][0]['user']['name'] : (order['order_comments'][0]['is_customer'] ? 'Customer' : 'AutoPilot')
                                }}:</b> {{ order['order_comments'][0]['comment'] }}
                            </div>

                            <div class="row text-center text-secondary" @click="toggleOrderDetails">
                                <div class="col">
                                    <font-awesome-icon v-if="orderDetailsVisible" icon="chevron-up" class="fa fa-xs"></font-awesome-icon>
                                    <font-awesome-icon v-if="!orderDetailsVisible" icon="chevron-down" class="fa fa-xs"></font-awesome-icon>
                                </div>
                            </div>

                            <div v-if="orderDetailsVisible">
                                <div class="row mb-2 mt-1">
                                    <input ref="newCommentInput" v-model="input_comment" class="form-control" :placeholder="$t('Add comment here')"
                                        @keypress.enter="addComment" />
                                </div>

                                <template v-for="order_comment in order['order_comments']">
                                    <div class="row mb-2">
                                        <div class="col">
                                            <b>{{
                                                order_comment['user'] ? order_comment['user']['name'] : (order_comment['is_customer'] ? 'Customer' : 'AutoPilot')
                                            }}:</b> {{ order_comment['comment'] }}
                                        </div>
                                    </div>
                                </template>

                                <div class="row tabs-container mb-2 mt-2 small">
                                    <ul class="nav nav-tabs">
                                        <li class="nav-item">
                                            <a class="nav-link active p-0 pl-1 pr-1" data-toggle="tab" href="#"
                                                @click.prevent="currentTab = 'productsOrdered'">
                                                {{ $t('Products') }}
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link p-0 pl-1 pr-1" data-toggle="tab" href="#"
                                                @click.prevent="currentTab = 'orderDetails'">
                                                {{ $t('Details') }}
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link p-0 pl-1 pr-1" data-toggle="tab" href="#"
                                                @click.prevent="currentTab = 'shippingAddress'">
                                                {{ $t('Address') }}
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link p-0 pl-1 pr-1" data-toggle="tab" href="#"
                                                @click.prevent="currentTab = 'orderActivities'">
                                                {{ $t('Activity') }}
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link p-0 pl-1 pr-1" target="_blank"
                                                :href="'/order/packsheet/' + order['id'] + '?hide_nav_bar=true'">
                                                {{ $t('Packsheet') }}
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link p-0 pl-1 pr-1" target="_blank"
                                                :href="'/order/packsheet/' + order['id'] + '?hide_nav_bar=true&return_scan_mode=true'">
                                                {{ $t('Return Sheet') }}
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a v-if="sharingAvailable()" class="nav-link p-0 pl-1 pr-1" @click.prevent="shareLink" href="#">
                                                <font-awesome-icon icon="share-alt" class="fas fa-sm"></font-awesome-icon>
                                            </a>
                                        </li>
                                    </ul>
                                </div>

                                <div v-if="currentTab === 'productsOrdered'">
                                    <template v-for="(order_product, index) in order_products">
                                        <div v-if="!order_product['parent_product_id']">
                                            <div class="row text-left mb-2">
                                                <div class="col-12">
                                                    <small>{{ order_product['name_ordered'] }} &nbsp;</small>
                                                    <!--                                                <div class="small"><a v-if="order_product['product_id']" target="_blank" :href="getProductLink(order_product)">{{order_product['sku_ordered'] }}</a><div v-if="order_product['product_id'] === null" class="bg-warning">{{order_product['sku_ordered'] }}</div>&nbsp;-->
                                                    <!--                                                </div>-->
                                                    <div class="small">
                                                        <a href="#" v-if="order_product['product_id']"
                                                            @click="showProductModal(order_product)">
                                                            {{ order_product['sku_ordered'] }}
                                                        </a>
                                                        <div v-if="order_product['product_id'] === null" class="bg-warning">
                                                            {{ order_product['sku_ordered'] }}
                                                        </div>&nbsp;
                                                    </div>
                                                </div>
                                                <div class="col">
                                                    <div class="row text-center">
                                                        <div class="col">
                                                            <number-card :label="$t('ordered')"
                                                                :number="order_product['quantity_ordered']"></number-card>
                                                        </div>
                                                        <div class="col bg-warning" v-if="Number(order_product['quantity_split']) > 0">
                                                            <number-card :label="$t('split')"
                                                                :number="order_product['quantity_split']"></number-card>
                                                        </div>
                                                        <div class="col d-none d-md-block text-right">
                                                            <div class="text-center w-100 text-secondary small">
                                                                <small class="small text-secondary">{{ $t('unit price') }}</small>
                                                            </div>
                                                            <span class="pr-0 mr-2 h4">{{
                                                                Math.floor(order_product['unit_sold_price'])
                                                            }}<span class="ml-0 pl-0" style="font-size: 8pt">.{{
                                                                (Math.round(order_product['unit_sold_price'] % 1 * 100)).toString().padStart(2, '0')
                                                            }}</span></span>
                                                        </div>
                                                        <div class="col">
                                                            <number-card :label="$t('picked')"
                                                                :number="order_product['quantity_picked']"></number-card>
                                                        </div>
                                                        <div class="col bg-warning"
                                                            v-if="Number(order_product['quantity_skipped_picking']) > 0">
                                                            <number-card :label="$t('skipped')"
                                                                :number="order_product['quantity_skipped_picking']"></number-card>
                                                        </div>
                                                        <div class="col d-none d-sm-block">
                                                            <number-card :label="$t('shipped')"
                                                                :number="order_product['quantity_shipped']"></number-card>
                                                        </div>
                                                        <div class="col">
                                                            <number-card :label="$t('to ship')"
                                                                :number="order_product['quantity_to_ship']"></number-card>
                                                        </div>
                                                        <div class="col" v-bind:class="{ 'bg-warning': ifHasEnoughStock(order_product) }">
                                                            <number-card :label="$t('inventory')"
                                                                :number="getProductQuantity(order_product)"></number-card>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <hr v-if="index > 0 && index < order_products.length - 1">
                                        </div>
                                    </template>
                                </div>

                                <order-addresses
                                    v-if="currentTab === 'shippingAddress'"
                                    :order="order"
                                    @open-modal="$emit('open-modal', $event)"
                                />

                                <div class="container" v-if="currentTab === 'orderDetails'">
                                    <div class="row">
                                        <div class="col">
                                            <column-wrapped :left-text="$t('status code')">
                                                <template #right-text>
                                                    <select id="selectStatus" class="form-control" @change="changeStatus" v-model="order.status_code">
                                                        <option v-for="orderStatus in order_statuses" :value="orderStatus.code" :key="orderStatus.id">
                                                            {{ orderStatus.code }}
                                                        </option>
                                                    </select>
                                                </template>
                                            </column-wrapped>

                                            <column-wrapped :leftText="$t('origin status code')" :rightText="order['origin_status_code']"/>
                                            <column-wrapped :leftText="$t('shipping method code')" :rightText="order['shipping_method_code']"/>
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

                                    <div class="mt-2 text-center font-weight-bod text-uppercase">
                                        <a :href="activityLogsLink">{{ $t('See All') }}</a>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="spacer-bottom" class="row mb-4 mt-4" v-if="orderDetailsVisible"></div>
            </div>
        </div>

        <modal-order-tags
            v-if="showOrderTagsModal"
            :order-tags="order.tags"
            :order-id="order.id"
            @closeModal="showOrderTagsModal = false"
            @tagsUpdated="tagsUpdated"
        />
    </div>

</template>

<script>
import api from "../../mixins/api";
import helpers from "../../mixins/helpers";
import url from "../../mixins/url";
import ModalOrderTags from "./ModalOrderTags.vue";
import ColumnWrapped from "./ColumnWrapped.vue";
import OrderAddresses from "./OrderAddresses.vue";

export default {
    mixins: [api, helpers, url],
    name: "OrderCard",

    components: {
        ColumnWrapped,
        ModalOrderTags,
        OrderAddresses
    },

    props: {
        order: {
            type: Object,
            required: true,
            default: () => ({}),
        },
        expanded: {
            type: Boolean,
            default: false,
        },
    },


    data: function () {
        return {
            input_comment: '',
            orderDetailsVisible: false,

            currentTab: 'productsOrdered',

            order_products: [],
            order_comments: null,
            order_activities: null,
            order_shipments: null,
            order_statuses: null,
            order_payments: null,

            showOrderTagsModal: false,
        }
    },

    mounted() {
        // we pre creating array of empty products so UI will display empty row for each orderProduct
        // its simply more pleasant to eye and card doesn't "jump"
        for (let i = 0; i < this.order['product_line_count']; i++)
            this.order_products.unshift([]);
    },

    created: function () {
        if (this.expanded) {
            this.toggleOrderDetails();
        }
    },

    watch: {
        expanded() {
            if (this.expanded) {
                this.toggleOrderDetails();
            }
        },
    },

    computed: {
        activityLogsLink() {
            return `/reports/activity-log?sort=-id&filter[subject_type]=App\\Models\\Order&filter[subject_id]=${this.order.id}`
        }
    },

    methods: {
        changeStatus() {
            this.apiUpdateOrder(this.order['id'], { 'status_code': this.order.status_code })
                .catch(() => {
                    this.apiActivitiesPost({
                        'subject_type': 'order',
                        'subject_id': this.order.id,
                        'description': this.$t('Error when changing status')
                    });
                    this.notifyError(this.$t('Error occurred when changing status'));
                });
        },

        loadOrderStatuses() {
            this.apiGetOrderStatus({
                'filter[hidden]': 0,
                'per_page': 999,
                'sort': 'code'
            })
                .then(({ data }) => {
                    this.order_statuses = data.data;
                })
        },

        shippingContentUrl: function (shipment) {
            return '/shipping-labels/' + shipment['id'];
        },

        sharingAvailable() {
            return navigator.share;
        },

        shareLink() {
            navigator.share({
                url: '/orders?search=' + this.order['order_number'],
                title: document.title
            });
        },

        toggleOrderDetails() {
            if (this.orderDetailsVisible) {
                this.orderDetailsVisible = false;
                return;
            }

            this.loadOrderProducts()
            this.loadOrderActivities();
            this.loadOrderShipments();
            this.loadOrderStatuses();
            this.loadOrderPayments();

            this.orderDetailsVisible = true;
        },

        loadOrderProducts() {
            let params = {
                'filter[warehouse_id]': this.getUrlParameter('warehouse_id'),
                'filter[order_id]': this.order['id'],
                'include': 'product',
                'per_page': '999',
            };

            this.apiGetOrderProducts(params)
                .then(({ data }) => {
                    this.order_products = data.data;
                })

            return this;
        },

        loadOrderComments() {
            if (this.order_comments) {
                return this;
            }

            let params = {
                'filter[order_id]': this.order['id'],
                'include': 'user',
                'sort': '-id',
                'per_page': '999',
            };

            this.apiGetOrderComments(params)
                .then(({ data }) => {
                    this.order_comments = data.data;
                })

            return this;
        },

        loadOrderActivities() {
            if (this.order_activities) {
                return this;
            }

            let params = {
                'filter[subject_id]': this.order['id'],
                'filter[subject_type]': 'App\\Models\\Order',
                'include': 'causer',
                'sort': '-id',
                'per_page': '999',
            };

            this.apiGetActivityLog(params)
                .then(({ data }) => {
                    this.order_activities = data.data;
                })

            return this;
        },

        loadOrderShipments() {
            if (this.order_shipments) {
                return this;
            }

            let params = {
                'filter[order_id]': this.order['id'],
                'include': 'user',
                'sort': '-id',
                'per_page': '999',
            };

            this.apiGetOrderShipments(params)
                .then(({ data }) => {
                    this.order_shipments = data.data;
                })

            return this;
        },

        hasSkippedPick(orderProduct) {
            return Number(orderProduct['quantity_skipped_picking']) > 0;
        },

        addComment() {
            let data = {
                "order_id": this.order['id'],
                "comment": this.input_comment
            };

            this.apiPostOrderComment(data)
                .then(({ data }) => {
                    // quick hack to immediately display comment
                    this.order['order_comments'].unshift(data['data'][0])
                    this.loadOrderComments();
                    this.input_comment = '';
                })
        },

        showProductModal(orderProduct) {
            this.$modal.showProductDetailsModal(orderProduct['product']['id']);
        },

        getProductQuantity(orderProduct) {
            if (this.getUrlParameter('warehouse_id')) {
                return orderProduct['inventory_source_quantity']
            }
            return orderProduct['product'] ? Number(orderProduct['product']['quantity']) : 0;
        },

        ifHasEnoughStock(orderProduct) {
            return this.getProductQuantity(orderProduct) < Number(orderProduct['quantity_to_ship']);
        },

        loadOrderPayments() {
            if (this.order_payments) {
                return this;
            }

            let params = {
                'filter[order_id]': this.order['id'],
                'sort': '-id',
                'per_page': '999',
            };

            this.apiGetOrderPayments(params)
                .then(({ data }) => {
                    this.order_payments = data.data;
                })

            return this;
        },

        openTagModal() {
            this.showOrderTagsModal = true;
            setTimeout(() => {
                this.$bvModal.show('order-tags-modal');
            }, 500);
        },

        tagsUpdated(tags) {
            this.order.tags = tags
        }
    },
}
</script>

<style scoped>
.header-row>div,
.col {
    /*border: 1px solid #76777838;*/
}

.col {
    background-color: #ffffff;
    /*border: 1px solid #76777838;*/
}

.nav-item {
    margin-right: unset;
}
</style>
