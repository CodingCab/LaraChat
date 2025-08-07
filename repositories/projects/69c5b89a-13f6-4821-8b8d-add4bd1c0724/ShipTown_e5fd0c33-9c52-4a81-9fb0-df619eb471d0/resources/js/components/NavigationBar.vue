<template>

    <div class="bg-primary" style="min-height: 37px">
        <nav class="navbar navbar-expand-md navbar-light mb-2 p-0 bg-primary responsive-text" style="z-index: 1021">
            <div class="container text-white text-nowrap flex-nowrap">
                <div class="d-flex mb-0 navbar-dark w-100 text-nowrap flex-nowrap">

                    <!-- Products -->
                    <div class="dropdown">
                        <a id="products_link" class="dropdown-toggle btn btn-primary px-2" href="#" role="button" data-toggle="dropdown"
                            aria-haspopup="true" aria-expanded="false">
                            {{ $t('Products') }}
                        </a>

                        <div class="dropdown">
                            <div class="dropdown-menu dropdown-menu-left w-auto text-left bg-primary" role="button" aria-labelledby="navbarDropdown">
                                <a v-for="menu in productsMenu" class="dropdown-item text-white lightHover mt-1" :id="menu.id" :href="menu.url" :key="menu.id">{{ menu.name }}</a>
                            </div>
                        </div>
                    </div>

                    <a id="orders_link" class="btn btn-primary px-2" @click.prevent='click("/orders?")'>{{ $t('Orders') }}</a>

                    <!-- Tools -->
                    <div class="dropdown" v-if="this.currentUser()['warehouse_id'] > 0">
                        <a id="tools_link" class="dropdown-toggle btn btn-primary px-2" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{ $t('Tools') }}</a>

                        <div class="dropdown-menu dropdown-menu-left w-auto text-left bg-primary " aria-labelledby="navbarDropdown">
                            <a v-for="(menu) in toolsMenu" class="dropdown-item text-white lightHover mt-1" :id="menu.id" :key="menu.id" :href="menu.url">{{ menu.name }}</a>
                        </div>
                    </div>

                    <!-- Middle empty fill -->
                    <div class="flex-fill"></div>

                    <!-- Reports Dropdown -->
                    <div class="dropdown">
                        <a id="reports_link" class="dropdown-toggle btn btn-primary px-2" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            {{ $t('Reports') }}
                        </a>

                        <div class="dropdown-menu dropdown-menu-right bg-primary" aria-labelledby="navbarDropdown">
                            <template v-for="(menu, index) in reportsDashboards">
                                <reports-dropdown-item :index="index" :menu="menu"/>
                            </template>
                            <template v-for="(menu, index) in reportsMenuAlphabeticallySorted">
                                <reports-dropdown-item :index="index" :menu="menu"/>
                            </template>
                            <template v-for="(menu, index) in reportsMenuCustom">
                                <reports-dropdown-item :index="index" :menu="menu"/>
                            </template>
                        </div>
                    </div>

                    <!-- Menu -->
                    <div class="dropdown dropdown-menu-right">
                        <a style="height: 37px; width: 40px; position: relative; top: -2px; right: 2px" id="dropdownMenu"
                            class="btn btn-primary px-2" href="#" role="button" data-toggle="dropdown" aria-haspopup="true"
                            aria-expanded="false">
                            <svg xmlns="http://www.w3.org/2000/svg" height="20" width="16" viewBox="0 0 448 512">
                                <path fill="#ffffff"
                                    d="M0 96C0 78.3 14.3 64 32 64H416c17.7 0 32 14.3 32 32s-14.3 32-32 32H32C14.3 128 0 113.7 0 96zM0 256c0-17.7 14.3-32 32-32H416c17.7 0 32 14.3 32 32s-14.3 32-32 32H32c-17.7 0-32-14.3-32-32zM448 416c0 17.7-14.3 32-32 32H32c-17.7 0-32-14.3-32-32s14.3-32 32-32H416c17.7 0 32 14.3 32 32z" />
                            </svg>
                        </a>

                        <div class="dropdown-menu dropdown-menu-right w-auto text-left bg-primary" aria-labelledby="navbarDropdown">
                            <a v-for="menu in settingsMenu" :id="menu.id" class="dropdown-item text-white lightHover" :href="menu.url" :target="menu.taget" :key="menu.key" @click="onSettingsMenuClick(menu, $event)">{{ menu.name }}</a>

                            <!-- Logout -->
                            <a class="dropdown-item text-white lightHover" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">{{ $t('Logout') }}</a>

                            <language-selector></language-selector>

                            <form id="logout-form" action="/logout" method="POST" style="display: none;">
                                <input type="hidden" name="_token" :value="csrf_token">
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
    </div>
</template>

<script>
import api from "../mixins/api";
import url from "../mixins/url";
import ReportsDropdownItem from "./ReportsDropdownItem.vue";

export default {
    name: 'NavigationBar',
    components: {ReportsDropdownItem},
    mixins: [api, url],
    props: {
        content: {
            required: false,
        },
    },
    data() {
        return {
            productsMenu: [],
            toolsMenu: [],
            reportsDashboards: [],
            reportsMenu: [],
            settingsMenu: [],
            reportsMenuCustom: []
        }
    },
    computed: {
        csrf_token() {
            return document.head.querySelector('meta[name="csrf-token"]').content;
        },

        reportsMenuAlphabeticallySorted() {
            return this.reportsMenu.sort((a, b) => {
                if (a.name === 'separator') return -1;
                if (b.name === 'separator') return 1;
                return a.name.localeCompare(b.name);
            });
        }
    },
    mounted() {
        this.productsMenu = [
            { name: this.$t('Inventory'), id: 'inventory_link', url: '/products/inventory?sort=-quantity' },
            { name: this.$t('Transfers In'), id: 'transfers_in_link', url: '/products/transfers-in?filter[type]=App\\Models\\DataCollectionTransferIn' },
            { name: this.$t('Transfers Out'), id: 'transfers_out_link', url: '/products/transfers-out?filter[type]=App\\Models\\DataCollectionTransferOut' },
            { name: this.$t('Purchase Orders'), id: 'purchases_order_link', url: '/products/purchase-orders?filter[type]=App\\Models\\DataCollectionPurchaseOrder' },
            { name: this.$t('Transactions'), id: 'transactions_link', url: '/products/transactions?filter[type]=App\\Models\\DataCollectionTransaction' },
            { name: this.$t('Stocktaking'), id: 'stocktaking_link', url: '/products/stocktaking?' },
        ];

        this.toolsMenu = [
            { name: this.$t('Picklist'), id: 'picklist_link', url: '/tools/picklist?step=select' },
            { name: this.$t('Packlist'), id: 'packlist_link', url: '/tools/packlist?step=select' },
            { name: this.$t('Restocking'), id: 'restocking_link', url: '/tools/restocking?sort=-quantity_required&filter[warehouse_has_stock]=1' },
            { name: this.$t('Data Collector'), id: 'data_collector_link', url: '/tools/data-collector?filter[without_transactions]=true' },
            { name: this.$t('Point Of Sale'), id: 'point_of_sale_link', url: '/tools/data-collector/transaction?' },
            { name: this.$t('Shelf Labels'), id: 'shelf_labels_link', url: '/tools/shelf-labels?' },
        ];

        this.settingsMenu = [
            { name: this.$t('Profile'), url: '/setting-profile', id: 'profile' },
            { name: this.$t('Settings'), url: '/settings', adminOnly: true, id: 'menu_settings_link' },
            { name: this.$t('Academy'), url: 'https://ship.town/academy', taget: '_blank', id: 'academy' },
            { name: this.$t('YouTube'), url: 'https://www.youtube.com/channel/UCl04S5dRXop1ZdZsOqY3OnA', taget: '_blank', id: 'youtube' },
            { name: this.$t('API Docs'), url: '/docs/index.html', taget: '_blank', id: 'api_docs' },
        ];

        this.reportsDashboards = [
            { name: this.$t('Inventory Dashboard'), id: 'inventory_dashboard_report', url: '/inventory-dashboard' },
            { name: this.$t('Orders Dashboard'), id: 'orders_dashboard_report', url: '/fulfillment-dashboard' },
            { name: this.$t('Fulfillment Statistics'), id: 'fulfillment_statistics_report', url: '/fulfillment-statistics?between_dates=-7days,now' },
            { name: this.$t('Scheduled Reports'), id: 'scheduled_reports_report', url: '/modules/scheduled-reports' },

            { name: this.$t('separator'), id: 'separator_2_report' }
        ];

        this.reportsMenu = [
            { name: this.$t('Assembly Products'), id: 'assembly_products_elements_report', url: `/reports/assembly-products-elements?sort=-assemblies_possible&filter[warehouse_code]=${this.currentUser()['warehouse_code']}` },
            { name: this.$t('Orders'), id: 'orders_report', url: '/reports/orders?sort=-order_placed_at' },
            { name: this.$t('Order Products'), id: 'order_products_report', url: '/reports/order-products?sort=-order_placed_at' },
            { name: this.$t('Order Picks'), id: 'order_picks_report', url: `/reports/picks?sort=-picked_at&filter[warehouse_code]=${this.currentUser()['warehouse_code']}` },
            { name: this.$t('Order Shipments'), id: 'order_shipments_report', url: '/reports/shipments?filter[created_at_between]=today,today 23:59:59&sort=-created_at' },
            { name: this.$t('Orders Fulfillment Time'), id: 'order_fulfillment_time_report', url: '/reports/order-fulfillment-time?filter[closed_at_between]=7 days ago,now&sort=status_code' },
            { name: this.$t('Product Totals'), id: 'inventory_totals_report', url: `/reports/products-inventory?sort=-quantity&per_page=999` },
            { name: this.$t('Purchase Orders'), id: 'purchase_orders_report', url: `/reports/purchase-orders?sort=-quantity&per_page=999` },
            { name: this.$t('Inventory'), id: 'inventory_report', url: `/reports/inventory?filter[warehouse_code]=${this.currentUser()['warehouse_code']}&sort=-quantity_warehouse` },
            { name: this.$t('Inventory Sales'), id: 'inventory_sales_report', url: `/reports/inventory-sales-summary?sort=department&filter[occurred_at_between]=today,today 23:59:59` },
            { name: this.$t('Inventory Transfers'), id: 'inventory_transfers_report', url: `/reports/inventory-transferred?filter[warehouse_code]=${this.currentUser()['warehouse_code']}&sort=-id` },
            { name: this.$t('Inventory Stocktakes'), id: 'inventory_stocktakes_report', url: `/reports/inventory-stocktakes?filter[warehouse_code]=${this.currentUser()['warehouse_code']}&filter[type]=stocktake&sort=-occurred_at&select=occurred_at,product_sku,product_name,quantity_before,quantity_delta,quantity_after,percentage_delta,total_price,total_cost,warehouse_code,description` },
            { name: this.$t('Inventory Movements'), id: 'inventory_movements_report', url: `/reports/inventory-movements?filter[warehouse_code]=${this.currentUser()['warehouse_code']}&sort=-occurred_at,-sequence_number` },
            { name: this.$t('Inventory Movements Summary'), id: 'inventory_movements_summary_report', url: `/reports/inventory-movements-summary?filter[warehouse_code]=${this.currentUser()['warehouse_code']}&filter[occurred_at_between]=today,today 23:59:59&per_page=200&sort=warehouse_code,type` },
            { name: this.$t('Inventory Movements Daily Statistics'), id: 'inventory_movements_daily_statistics', url: `/reports/inventory-movements-daily-statistics?filter[warehouse_code]=${this.currentUser()['warehouse_code']}&filter[date_between]=yesterday,yesterday 23:59:59` },
            { name: this.$t('Inventory Reservations'), id: 'inventory_reservations_report', url: `/reports/inventory-reservations?filter[warehouse_code]=${this.currentUser()['warehouse_code']}` },
            { name: this.$t('Restocking'), id: 'restocking_report', url: `/reports/restocking?sort=-warehouse_has_stock,-quantity_required,-quantity_incoming,-fc_quantity_available&filter[warehouse_has_stock]=true` },
            { name: this.$t('Stocktake Suggestions'), id: 'stocktake_suggestions_report', url: `/reports/stocktake-suggestions?filter[warehouse_code]=${this.currentUser()['warehouse_code']}&sort=-points` },
            { name: this.$t('Activity Log'), id: 'activity_log_report', url: '/reports/activity-log' },
            { name: this.$t('Data Collections'), id: 'data_collections_report', url: '/reports/data-collections?sort=-created_at' },
            { name: this.$t('Data Collections Records'), id: 'data_collections_records_report', url: '/reports/data-collections-records' },
            { name: this.$t('Heartbeats'), id: 'heartbeats_report', url: '/reports/heartbeats?sort=-expires_at' },
        ];
        this.getReportsCustomNavigationLinks();
    },
    methods: {
        getReportsCustomNavigationLinks() {
            return this.apiGetNavigationMenu({
                'filter[group]': 'reports',
                'per_page': 100,
            })
            .then((response) => {
                this.reportsMenuCustom = [
                    ...this.reportsMenuCustom,
                    { name: 'separator' },
                    ...response.data.data.map(menu => {
                        return {
                            name: menu.name,
                            url: menu.url,
                        }
                    })
                ];
            })
            .catch((error) => {
                this.displayApiCallError(error);
            });
        },

        onSettingsMenuClick(menu, event) {
            if (menu.id === 'menu_settings_link') {
                event.preventDefault();
                this.$modal.showSettingsModal();
            }
        }
    }
}
</script>

<style>
.responsive-text a {
    font-size: 10pt;
}

@media (max-width: 768px) {
    .responsive-text a {
        font-size: 9pt;
    }
}
</style>
