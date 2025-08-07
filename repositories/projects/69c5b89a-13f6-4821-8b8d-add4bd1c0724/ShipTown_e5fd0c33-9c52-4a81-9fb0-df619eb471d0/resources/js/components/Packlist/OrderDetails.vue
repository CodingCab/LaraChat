<template>
    <div v-if="order">
        <div class="card border-top-0">
            <div class="row rounded p-2">
                <div class="col-lg-6 text-wrap">
                    <column-wrapped>
                        <template #left-text>
                            <div class="text-primary font-weight-bold">
                                <font-awesome-icon icon="copy" class="fa-xs" role="button" @click="copyToClipBoard(order['order_number'])"></font-awesome-icon>
                                <a :href="'/orders?search=' + order['order_number']">{{ order['order_number'] }}</a>
                            </div>
                        </template>

                        <template #right-text>
                            <div class="small font-weight-bold">{{ order['status_code'] }}</div>
                        </template>
                    </column-wrapped>
                    <column-wrapped>
                        <template #left-text>
                            <div class="small font-weight-bold">{{ formatDateTime(order['order_placed_at']) }}</div>
                        </template>

                        <template #right-text>
                            <div class="small">{{ order['label_template'] ? order['label_template'] : '&nbsp;' }}</div>
                        </template>
                    </column-wrapped>

                    <column-wrapped>
                        <template #right-text>
                            <template v-for="shipment in order['order_shipments']">
                                <a :href="shipment['tracking_url']" target="_blank" class="d-block">{{ shipment['shipping_number'] }}</a>
                            </template>
                        </template>
                    </column-wrapped>
                </div>

                <div class="col-lg-6 text-right small">
                    <number-card :number="order['age_in_days']" :label="$t('age')"/>
                    <div class="d-none d-md-inline-block">
                        <text-card :label="$t('total paid')">
                            <span class="pr-0 mr-2 h4 w-100">{{
                                    Math.floor(order['total_paid']) }}<span class="" style="font-size: 8pt"><template
                                    v-if="(order['total_paid']) % 1 === 0"> .00</template><template
                                    v-if="(order['total_paid']) % 1 > 0"> .{{ Math.floor((order['total_paid']) % 1 * 100) }} </template></span></span>
                        </text-card>
                    </div>
                    <number-card :number="order['order_products_totals']['count']" :label="$t('lines')"/>
                    <number-card :number="order['order_products_totals'][ 'quantity_ordered']" :label="$t('ordered')"/>
                    <div class="d-none d-md-inline-block">
                        <number-card :number="order['order_products_totals']['quantity_picked']" :label="$t('picked')" ></number-card>
                        <number-card :number="order['order_products_totals']['quantity_shipped']" :label="$t('shipped')" ></number-card>
                    </div>
                    <number-card :number="order['order_products_totals']['quantity_to_ship']" :label="$t('to ship')"/>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import helpers from "../../mixins/helpers";
    import ColumnWrapped from "../Orders/ColumnWrapped.vue";

    export default {
        name: "OrderDetails",
        components: {ColumnWrapped},
        mixins: [helpers],

        props: {
            order: Object,
        },
    }
</script>
