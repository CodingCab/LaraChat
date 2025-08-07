<template>
    <div>
        <report @dataChanged="updateData" :params="params" :report-url="'/reports/restocking'">
            <div v-if="reportData != null">
                <template v-if="!isLoading" v-for="record in reportData['records']">
                    <restocking-record-card :record="record" @showModalMovement=showRecentInventoryMovementsModal></restocking-record-card>
                </template>
            </div>
        </report>
    </div>
</template>

<script>
import loadingOverlay from '../mixins/loading-overlay';
import url from "../mixins/url";

import RestockingRecordCard from "./RestockingPage/RestockingRecordCard.vue";
import api from "../mixins/api.vue";

export default {
        mixins: [loadingOverlay, url, api],

        components: {
            RestockingRecordCard,
        },

        props: {
            initial_data: null,
        },

        data: function() {
            return {
                params: {
                    'select': 'product_sku,product_name,price,sale_price,sale_start_date,sale_end_date,product_,warehouse_code,reorder_point,restock_level,quantity_in_stock,quantity_available,quantity_incoming,quantity_required,last_movement_at,last_sold_at,first_sold_at,last_counted_at,first_received_at,last_received_at,last_7_days_sales_quantity_delta,last_14_days_sales_quantity_delta,last_28_days_sales_quantity_delta,quantity_sold_last_7_days,quantity_sold_last_14_days,quantity_sold_last_28_days,warehouse_quantity,warehouse_has_stock,fc_shelf_location,id,product_id,inventory_id,warehouse_id,fc_quantity_available,fc_quantity_incoming,fulfilment_center',
                    'include': 'product,product.tags',
                },
                reportData: null,
            };
        },

        watch: {
            '$route' (to, from) {
                this.reportData = null; // Reset report data when route changes
            },
        },

        beforeMount() {
            if (this.currentUser()['warehouse_code'] !== null) {
                this.params['filter[warehouse_code]'] = this.currentUser()['warehouse_code'];
            }

            this.setUrlParameters({
                'filter[warehouse_has_stock]': this.getUrlParameter('filter[warehouse_has_stock]', true),
                'sort': this.getUrlParameter('sort','-warehouse_has_stock,-quantity_required,-quantity_incoming,-fc_quantity_available'),
            });
        },

        methods: {
            updateData(reportData) {
                this.reportData = reportData;
            },

            showRecentInventoryMovementsModal(inventory_id) {
                this.$modal.showRecentInventoryMovementsModal(inventory_id);
            },
        },
    }
</script>
