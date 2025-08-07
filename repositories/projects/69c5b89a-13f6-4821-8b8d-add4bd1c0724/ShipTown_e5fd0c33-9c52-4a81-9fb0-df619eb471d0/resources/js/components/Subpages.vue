<template>
    <main class="py-0 pl-1 pr-2 container justify-content-center">
        <products-table v-if="url === '/products/inventory'"/>
        <orders-table v-else-if="url === '/orders'"/>
        <transfers-in-list-page v-else-if="url === '/products/transfers-in'" />
        <transfers-out-list-page v-else-if="url === '/products/transfers-out'" />
        <offline-inventory-list-page v-else-if="url === '/products/offline-inventory'" />
        <purchase-order-list-page v-else-if="url === '/products/purchase-orders'"/>
        <transactions-list-page v-else-if="url === '/products/transactions'"/>
        <stocktaking-page v-else-if="url === '/products/stocktaking'"/>
        <picks-table v-else-if="url === '/tools/picklist'"/>
        <autopilot-packlist-page v-else-if="url === '/tools/packlist'"/>
        <data-collector-list-page v-else-if="url === '/tools/data-collector'"/>
        <tools-point-of-sale-page v-else-if="url === '/tools/data-collector/transaction'"/>
        <shelf-label-printing-page v-else-if="url === '/tools/shelf-labels'"/>
        <restocking-page v-else-if="url === '/tools/restocking'"/>

        <div v-else>
            <slot></slot>
        </div>
    </main>
</template>
<script>
import url from "../mixins/url";
import PurchaseOrderListPage from "./PurchaseOrderListPage.vue";
import TransfersInListPage from "./TransfersInListPage.vue";
import TransfersOutListPage from "./TransfersOutListPage.vue";
import OfflineInventoryListPage from "./OfflineInventoryListPage.vue";
import TransactionsListPage from "./TransactionsListPage.vue";

export default {
    name: 'subpages',
    components: {
        TransactionsListPage,
        OfflineInventoryListPage, TransfersOutListPage, TransfersInListPage, PurchaseOrderListPage: PurchaseOrderListPage},

    mixins: [url],

    data() {
        return {
            url: null,
        }
    },

    mounted() {
        this.url = this.$router.currentRoute.path;
    },

    watch: {
        '$route' (to, from) {
            // this.currentRoute = '';
            // wait for the next tick to set the currentRoute
            this.$nextTick(() => {
                this.url = to.path;
            });
        }
    },
}
</script>
