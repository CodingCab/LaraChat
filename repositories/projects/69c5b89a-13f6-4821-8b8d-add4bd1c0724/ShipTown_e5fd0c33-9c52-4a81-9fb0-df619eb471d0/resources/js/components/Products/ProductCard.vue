<template>
    <div :class="!forModal ? 'card p-2' : 'row p-1'">
        <div class="col pl-1">
            <div class="row text-left">
                <div class="col-lg-5 mb-2" :class="forModal ? '' : 'col-md-6'">
                    <product-info-card :product="product"></product-info-card>
                </div>
                <div class="col-lg-7" :class="forModal ? '' : 'col-md-6'">
                    <div class="table-responsive small">
                        <table class="table table-borderless mb-0 w-100 text-right">
                            <thead>
                                <tr class="small font-weight-bold">
                                    <th @click="toggleProductDetails" class="text-left">{{ $t('Location') }}</th>
                                    <th @click="openShelfLabelModal" class="text-left cursor-pointer">{{ $t('Shelf') }}</th>
                                    <th @click="toggleProductDetails" class="text-right">{{ $t('Available') }}</th>
                                    <th @click="toggleProductDetails" class="text-right d-none d-md-table-cell">{{ $t('Reserved') }}</th>
                                    <th @click="toggleProductDetails" class="text-right pr-1">{{ $t('Incoming') }}</th>
                                    <th @click="toggleProductDetails" class="text-right d-none d-md-table-cell pr-1">{{ $t('Required') }}
                                    </th>
                                    <th @click="toggleProductDetails" class="text-right">{{ $t('Price') }}</th>
                                    <th @click="toggleProductDetails" class="text-right">{{ $tc('{n} day', 7) }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="inventory in product.inventory" :key="inventory.id"
                                    v-bind:class="{ 'table-active': currentUser()['warehouse'] && inventory['warehouse_code'] === currentUser()['warehouse']['code'] }">
                                    <td class="text-left"><a class="text-primary cursor-pointer"
                                            :dusk="`show-inventory-movements-${inventory['id']}`"
                                            @click.prevent="showInventoryMovementModal(inventory['id'])">{{ inventory['warehouse_code']
                                            }}</a></td>
                                    <td @click="toggleProductDetails" class="text-left">{{ inventory['shelf_location'] }}</td>
                                    <td @click="toggleProductDetails">{{ toNumberOrDash(inventory['quantity_available']) }}</td>
                                    <td class="d-none d-md-table-cell">
                                        <a v-if="inventory['quantity_reserved'] !== 0" class="text-primary cursor-pointer" @click.prevent="showInventoryReservationsModal(inventory['id'])">
                                            {{ toNumberOrDash(inventory['quantity_reserved']) }}
                                        </a>
                                        <template v-else>
                                            {{ toNumberOrDash(inventory['quantity_reserved']) }}
                                        </template>
                                    </td>
                                    <td @click="toggleProductDetails" class="pr-1">{{ toNumberOrDash(inventory['quantity_incoming']) }}
                                    </td>
                                    <td @click="toggleProductDetails" class="d-none d-md-table-cell pr-1">{{
                                        toNumberOrDash(inventory['quantity_required']) }}</td>
                                    <td @click="toggleProductDetails" class="ml-2 pl-2"
                                        :class="{ 'bg-warning': product.prices[inventory['warehouse_code']]['is_on_sale'] === true }">{{
                                            toNumberOrDash(product.prices[inventory['warehouse_code']]['current_price'], 2) }}</td>
                                    <td @click="toggleProductDetails" class="ml-2">
                                        <template v-for="inventory_statistic in product['inventoryMovementsStatistics']">
                                            <div :key="inventory_statistic.id"
                                                v-if="inventory_statistic['type'] === 'sale' && inventory['warehouse_code'] === inventory_statistic['warehouse_code']">
                                                {{ toNumberOrDash(inventory_statistic['last7days_quantity_delta'] * (-1)) }}</div>
                                        </template>
                                    </td>
                                </tr>

                                <tr class="text-right"
                                    v-if="product['inventoryTotals'] && product['inventoryTotals'].length > 0 && product['inventory'].length > 1">
                                    <td class="text-left font-weight-bold"></td>
                                    <td class="text-left font-weight-bold"></td>
                                    <td class="font-weight-bold">{{ toNumberOrDash(product['inventoryTotals'][0]['quantity_available']) }}
                                    </td>
                                    <td class="d-none d-md-table-cell">{{
                                        toNumberOrDash(product['inventoryTotals'][0]['quantity_reserved']) }}</td>
                                    <td class="pr-1">{{ toNumberOrDash(product['inventoryTotals'][0]['quantity_incoming']) }}</td>
                                    <td class="d-none d-md-table-cell pr-1">{{
                                        toNumberOrDash(product['inventoryTotals'][0]['quantity_required']) }}</td>
                                    <td class="ml-2 pl-2"></td>
                                    <td class="ml-2"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div @click="toggleProductDetails" class="row-col text-center text-secondary show-detail">
                        <font-awesome-icon v-if="showDetails" icon="chevron-up" class="fa fa-xs"></font-awesome-icon>
                        <font-awesome-icon v-if="!showDetails" icon="chevron-down" class="fa fa-xs"></font-awesome-icon>
                    </div>

                    <div class="row-col" v-if="showDetails">
                        <div class="row-col tabs-container mb-2 flex-nowrap">
                            <ul class="nav nav-tabs flex-wrap mr-0 small">
                                <li class="nav-item mt-2">
                                    <a class="nav-link p-0 pl-1 pr-1 pr-lg-2 active" @click.prevent="currentTab = 'inventory'"
                                        data-toggle="tab" href="#" :dusk="`inventory-tab-${product.id}`">
                                        {{ $t('Inventory') }}
                                    </a>
                                </li>
                                <li class="nav-item mt-2">
                                    <a class="nav-link p-0 pl-1 pr-1 pr-lg-2" @click.prevent="currentTab = 'order'" data-toggle="tab"
                                        href="#" :dusk="`order-tab-${product.id}`">
                                        {{ $t('Orders') }}
                                    </a>
                                </li>
                                <li class="nav-item mt-2">
                                    <a class="nav-link p-0 pl-1 pr-1 pr-lg-2" @click.prevent="currentTab = 'prices'" data-toggle="tab"
                                        href="#" :dusk="`prices-tab-${product.id}`">
                                        {{ $t('Pricing') }}
                                    </a>
                                </li>
                                <li class="nav-item mt-2">
                                    <a class="nav-link p-0 pl-1 pr-1 pr-lg-2"
                                        @click.prevent="currentTab = 'aliases'; setFocusElementById('newProductAliasInput');"
                                        data-toggle="tab" href="#" :dusk="`aliases-tab-${product.id}`">
                                        {{ $t('Aliases') }}
                                    </a>
                                </li>
                                <li class="nav-item mt-2">
                                    <a class="nav-link p-0 pl-1 pr-1 pr-lg-2" @click.prevent="currentTab = 'labels'" data-toggle="tab"
                                        href="#" :dusk="`labels-tab-${product.id}`">
                                        {{ $t('Labels') }}
                                    </a>
                                </li>
                                <li class="nav-item mt-2">
                                    <a class="nav-link p-0 pl-1 pr-1 pr-lg-2" @click.prevent="currentTab = 'activityLog'" data-toggle="tab"
                                        href="#" :dusk="`activityLog-tab-${product.id}`">
                                        {{ $t('Activity') }}
                                    </a>
                                </li>
                                <li class="nav-item mt-2">
                                    <a class="nav-link p-0 pl-1 pr-1 pr-lg-2" @click.prevent="currentTab = 'weight'" data-toggle="tab"
                                        href="#" :dusk="`weight-tab-${product.id}`">
                                        {{ $t('Weight & Size') }}
                                    </a>
                                </li>
                                <li class="nav-item mt-2">
                                    <a class="nav-link p-0 pl-1 pr-1 pr-lg-2" @click.prevent="currentTab = 'assembly'" data-toggle="tab"
                                        href="#" :dusk="`assmbly-tab-${product.id}`">
                                        {{ $t('Assembly') }}
                                    </a>
                                </li>
                                <li class="nav-item mt-2">
                                    <a class="nav-link p-0 pl-1 pr-1 pr-lg-2" @click.prevent="editProduct" data-toggle="tab" href="#">
                                        {{ $t('Edit') }}
                                    </a>
                                </li>
                                <li class="nav-item mt-2">
                                    <a v-if="sharingAvailable()" @click.prevent="shareLink" class="nav-link p-0 pl-1 pr-1 pr-lg-2" href="#">
                                        <font-awesome-icon icon="share-alt" class="fas fa-sm"></font-awesome-icon>
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <template v-if="currentTab === 'inventory'">
                            <div class="table-responsive" :dusk='`inventory-detail-${product.id}`'>
                                <table class="table table-borderless mb-0 w-100 text-right small">
                                    <thead>
                                        <tr class="small font-weight-bold">
                                            <th class="text-left">{{ $t('Warehouse') }}</th>
                                            <th class="d-table-cell d-md-none">RL</th>
                                            <th class="d-none d-md-table-cell">{{ $t('Restock Level') }}</th>

                                            <th class="d-table-cell d-md-none pl-2">RP</th>
                                            <th class="d-none d-md-table-cell">{{ $t('Reorder Point') }}</th>

                                            <th>{{ $t('In Stock') }}</th>
                                            <th class="d-none d-md-table-cell">{{ $t('Reserved') }}</th>
                                            <th>{{ $t('Incoming') }}</th>
                                            <th>{{ $t('Required') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="inventory in product.inventory" :key="inventory.id" :dusk="`inventory-${inventory.id}`"
                                            class=""
                                            v-bind:class="{ 'table-active': currentUser()['warehouse'] && inventory['warehouse_code'] === currentUser()['warehouse']['code'] }">
                                            <td class="text-left">{{ inventory['warehouse_code'] }}</td>
                                            <td>
                                                <template v-if="currentUser()['warehouse'] && inventory['warehouse_code'] === currentUser()['warehouse']['code']">
                                                    <div class="number-spinner">
                                                        <button class="btn btn-light btn-sm p-0 px-1" type="button"
                                                                @click="adjustRestockLevel(inventory, -1)">-</button>
                                                        <input type="number" class="form-control form-small text-right mx-1"
                                                               v-model.number="inventory['restock_level']"
                                                               @change="updateInventoryLevels(inventory)"/>
                                                        <button class="btn btn-light btn-sm p-0 px-1" type="button"
                                                                @click="adjustRestockLevel(inventory, 1)">+</button>
                                                    </div>
                                                </template>
                                                <template v-else>
                                                    {{ toNumberOrDash(inventory['restock_level']) }}
                                                </template>
                                            </td>
                                            <td>
                                                <template v-if="currentUser()['warehouse'] && inventory['warehouse_code'] === currentUser()['warehouse']['code']">
                                                    <div class="number-spinner">
                                                        <button class="btn btn-light btn-sm p-0 px-1" type="button"
                                                                @click="adjustReorderPoint(inventory, -1)">-</button>
                                                        <input type="number" class="form-control form-small text-right mx-1"
                                                               v-model.number="inventory['reorder_point']"
                                                               @change="updateInventoryLevels(inventory)"/>
                                                        <button class="btn btn-light btn-sm p-0 px-1" type="button"
                                                                @click="adjustReorderPoint(inventory, 1)">+</button>
                                                    </div>
                                                </template>
                                                <template v-else>
                                                    {{ toNumberOrDash(inventory['reorder_point']) }}
                                                </template>
                                            </td>
                                            <td>{{ toNumberOrDash(inventory['quantity']) }}</td>
                                            <td class="d-none d-md-table-cell">
                                                <a v-if="inventory['quantity_reserved'] !== 0" class="text-primary cursor-pointer" @click.prevent="showInventoryReservationsModal(inventory['id'])">
                                                    {{ toNumberOrDash(inventory['quantity_reserved']) }}
                                                </a>
                                                <template v-else>
                                                    {{ toNumberOrDash(inventory['quantity_reserved']) }}
                                                </template>
                                            </td>
                                            <td>{{ toNumberOrDash(inventory['quantity_incoming']) }}</td>
                                            <td>{{ toNumberOrDash(inventory['quantity_required']) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </template>

                        <template v-if="currentTab === 'order'">
                            <div class="table-responsive" :dusk='`order-detail-${product.id}`'>
                                <template>
                                    <div v-for="orderProduct in orders" :key="orderProduct.id" :dusk="`order-product-${orderProduct.id}`">
                                        <div class="row text-left mb-2">
                                            <div class="col-5">
                                                <div>
                                                    <a target="_blank" :href="getProductLink(orderProduct)">
                                                        #{{ orderProduct['order']['order_number'] }}
                                                    </a>
                                                </div>
                                                <div class="small">
                                                    {{ formatDateTime(orderProduct['order']['order_placed_at'], 'MMM DD') }}
                                                </div>
                                                <div class="small">
                                                    {{ orderProduct['order']['status_code'] }}
                                                </div>
                                            </div>
                                            <div class="col-7">
                                                <div class="row justify-content-end text-center small">
                                                    <div class="cold d-none d-sm-block">
                                                        <small>{{ $t('ordered') }}</small>
                                                        <h3>{{ Math.ceil(orderProduct['quantity_ordered']) }}</h3>
                                                    </div>
                                                    <div class="col">
                                                        <small>{{ $t('picked') }}</small>
                                                        <h3>{{ dashIfZero(Number(orderProduct['quantity_picked'])) }}</h3>
                                                    </div>
                                                    <div class="col">
                                                        <small>{{ $t('skipped') }}</small>
                                                        <h3>{{ dashIfZero(Number(orderProduct['quantity_skipped_picking'])) }}</h3>
                                                    </div>
                                                    <div class="col d-none d-sm-block">
                                                        <small>{{ $t('shipped') }}</small>
                                                        <h3>{{ dashIfZero(Number(orderProduct['quantity_shipped'])) }}</h3>
                                                    </div>
                                                    <div class="col">
                                                        <small>{{ $t('to ship') }}</small>
                                                        <h3>{{ dashIfZero(Number(orderProduct['quantity_to_ship'])) }}</h3>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-if="!orders.length" class="text-center text-secondary small">
                                        {{ statusMessageOrder ? statusMessageOrder : 'No orders found' }}
                                    </div>
                                    <div v-else class="mt-2 text-center font-weight-bod text-uppercase">
                                        <a :href="orderProductReportLink">{{ $t('See All') }}</a>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <template v-if="currentTab === 'prices'">
                            <div class="table-responsive" :dusk='`prices-detail-${product.id}`'>
                                <table class="table table-borderless mb-0 w-100 small">
                                    <thead>
                                        <tr class="small font-weight-bold">
                                            <th>Location</th>
                                            <th class="text-right pr-1">{{ $t('Cost') }}</th>
                                            <th class="text-right pr-1">{{ $t('Price') }}</th>
                                            <th class="text-right">{{ $t('Sale Price') }}</th>
                                            <th class="text-right">{{ $t('Start Date') }}</th>
                                            <th class="text-right">{{ $t('End Date') }}</th>
                                            <th class="text-right">{{ $t('Tax Code') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="price in product['prices']" :dusk="`price-${price.id}`" :key="price.id"
                                            v-bind:class="{ 'table-active': currentUser()['warehouse'] && price['warehouse_code'] === currentUser()['warehouse']['code'] }">
                                            <td>{{ price['warehouse_code'] }}</td>
                                            <td class="text-right pr-1">{{ price['cost'] }}</td>
                                            <td class="text-right pr-1">{{ price['price'] }}</td>
                                            <td class="text-right" :class="{ 'bg-warning': price['is_on_sale'] }">{{ price['sale_price'] }}
                                            </td>
                                            <td class="text-right">{{ formatDateTime(price['sale_price_start_date'], 'YYYY MMM D') }}</td>
                                            <td class="text-right">{{ formatDateTime(price['sale_price_end_date'], 'YYYY MMM D') }}</td>
                                            <td class="text-right">{{ price['sales_tax_code'] }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </template>

                        <template v-if="currentTab === 'aliases'">
                            <div class="container" :dusk='`aliases-detail-${product.id}`'>
                                <barcode-input-field :input_id="`newProductAliasInput-${product.id}`" class="newProductAliasInput" :placeholder="$t('Add new alias here')" ref="barcode" @barcodeScanned="addAliasToProduct"/>

                                <table class="mt-2">
                                    <tr v-for="alias in product.aliases" :key="alias.id" :dusk="`alias-${alias.id}`">
                                        <td valign="middle">
                                            <font-awesome-icon :dusk="`delete-alias-${alias.id}`" icon="x"
                                                class="fa-xs text-danger mr-1 cursor-pointer"
                                                @click="deleteAlias(alias)"></font-awesome-icon>
                                            <div class="badge mb-2">{{ alias.alias }}</div>
                                        </td>
                                        <td>
                                            <input type="number" class="form-control small quantityPackAliasInput mb-2"
                                                :placeholder="$t('Pack quantity here')" @change="changeQuantityAlias(alias)"
                                                @focus="$event.target.select()" v-model.number="alias.quantity"
                                                :dusk="`alias-quantity-input-${alias.id}`" />
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </template>

                        <template v-if="currentTab === 'assembly'">
                            <barcode-input-field :input_id="`assembly-input-${product.id}`" class="newProductAliasInput" :placeholder="$t('Add component product here')" ref="barcode" @barcodeScanned="addAssemblyProduct" :showManualSearchButton="true"/>
                            <div class="container" :dusk='`assembly-${product.id}`'>
                                <table class="mt-2">
                                    <tr v-for="assemblyProduct in product.assemblyProducts" :key="assemblyProduct.id" :dusk="`assembly-${assemblyProduct.id}`">
                                        <td valign="middle">
                                            <font-awesome-icon @click="removeAssemblyProduct(assemblyProduct.id)" :dusk="`assembly-${assemblyProduct.id}`" icon="x" class="fa-xs text-danger mr-1 cursor-pointer"></font-awesome-icon>
                                            <div class="badge mb-2">{{ assemblyProduct.simpleProduct.name }} SKU: {{ assemblyProduct.simpleProduct.sku }}</div>
                                        </td>
                                        <td>
                                            <input type="number" class="form-control small quantityPackAliasInput mb-2"
                                                :placeholder="$t('Quantity here')" @change="updateAssemblyProductElementQuantity(assemblyProduct)"
                                                @focus="$event.target.select()" v-model.number="assemblyProduct.required_quantity"
                                                min="1" :dusk="`assembly-quantity-input-${assemblyProduct.id}`" />
                                        </td>
                                    </tr>
                                </table>

                                <b-btn variant="primary" @click="showAssembleProductQuantityModal">Assemble Product</b-btn>
                                <b-btn variant="secondary" @click="showDisassembleProductQuantityModal">Disassemble Product</b-btn>
                            </div>
                        </template>

                        <template v-if="currentTab === 'labels'">
                            <div class="container" :dusk='`labels-detail-${product.id}`'>
                                <product-label-preview :product="product"></product-label-preview>
                            </div>
                        </template>

                        <template v-if="currentTab === 'activityLog'">
                            <div :dusk='`activityLog-detail-${product.id}`'>
                                <div class="row small" v-for="activity in activityLog" :key="activity.id" :dusk="`activity-${activity.id}`">
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
                                        v-for="(value, name) in activity['properties']['attributes'] ? activity['properties']['attributes'] : activity['properties']"
                                        :key="name">
                                        {{ name }} = {{ value }}
                                    </div>
                                </div>

                                <div class="mt-2 text-center font-weight-bod text-uppercase">
                                    <a :href="activityLogsLink">{{ $t('See All') }}</a>
                                </div>
                            </div>
                        </template>

                        <template v-if="currentTab === 'weight'">
                            <table :dusk='`weight-detail-${product.id}`'>
                                <tr>
                                    <td width="120" class="small">{{ $t('Weight') }}</td>
                                    <td>
                                        <input type="number" class="form-control form-small"
                                        :placeholder="$t('Weight (Kg)')" @change="changeProductWeightSize(product)"
                                        @focus="$event.target.select()" v-model.number="product.weight"
                                        dusk="product-weight-input" />
                                    </td>
                                </tr>
                                <tr>
                                    <td class="small">{{ $t('Length') }}</td>
                                    <td>
                                        <input type="number" class="form-control form-small"
                                        :placeholder="$t('Length')" @change="changeProductWeightSize(product)"
                                        @focus="$event.target.select()" v-model.number="product.length"
                                        dusk="product-length-input" />
                                    </td>
                                </tr>
                                <tr>
                                    <td class="small">{{ $t('Width') }}</td>
                                    <td>
                                        <input type="number" class="form-control form-small"
                                        :placeholder="$t('Width')" @change="changeProductWeightSize(product)"
                                        @focus="$event.target.select()" v-model.number="product.width"
                                        dusk="product-width-input" />
                                    </td>
                                </tr>
                                <tr>
                                    <td class="small">{{ $t('Height') }}</td>
                                    <td>
                                        <input type="number" class="form-control form-small"
                                            :placeholder="$t('Height')" @change="changeProductWeightSize(product)"
                                            @focus="$event.target.select()" v-model.number="product.height"
                                            dusk="product-height-input" />
                                    </td>
                                </tr>
                                <tr>
                                    <td class="small">{{ $t('Carton Quantity') }}</td>
                                    <td>
                                        <input type="number" class="form-control form-small"
                                            :placeholder="$t('Carton Quantity')" @change="changeProductWeightSize(product)"
                                            @focus="$event.target.select()" v-model.number="product.pack_quantity"
                                            dusk="product-pack-quantity-input" />
                                    </td>
                                </tr>
                                <tr>
                                    <td class="small">{{ $t('Volumetric Weight') }}</td>
                                    <td>
                                        <input type="number" class="form-control form-small"
                                            :value="this.product.height * this.product.width * this.product.length"
                                        dusk="product-volumetric-weight-input"
                                        disabled />
                                    </td>
                                </tr>
                            </table>
                        </template>
                    </div>
                </div>
            </div>

            <div class="row" v-if="showDetails">
                <div class="col offset-md-6 offset-lg-5">
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import api from "../../mixins/api";
import helpers from "../../mixins/helpers";
import VuePdfEmbed from "vue-pdf-embed/dist/vue2-pdf-embed";
import ProductLabelPreview from "../Tools/LabelPrinter/ProductLabelPreview.vue";
import BarcodeInputField from "../SharedComponents/BarcodeInputField.vue";

export default {
    name: "ProductCard",
    components: { ProductLabelPreview, VuePdfEmbed, BarcodeInputField },

    mixins: [api, helpers],

    props: {
        product: {
            type: Object,
            default: () => ({})
        },
        expanded: {
            type: Boolean,
            default: false
        },
        ordered: {
            type: Number,
            default: 0
        },
        forModal: {
            type: Boolean,
            default: false
        }
    },

    mounted: function () {
        this.currentTab = 'inventory';
    },

    watch: {
        currentTab() {
            switch (this.currentTab) {
                case 'orders':
                    if (!this.activeOrderProducts.length) {
                        this.loadActiveOrders();
                        this.loadCompletedOrders(10);
                    }
                    break;
                case 'activityLog':
                    if (!this.activityLog.length) {
                        this.loadActivityLog();
                    }
                    break;
                default:
                    break;
            }
        }
    },

    data: function () {
        return {
            pdfLabelBlob: null,
            statusMessageOrder: '',
            statusMessageActivity: '',
            activityLog: [],
            currentTab: '',
            showDetails: false,
            activeOrderProducts: [],
            completeOrderProducts: []
        };
    },

    computed: {
        orders() {
            return this.activeOrderProducts.concat(this.completeOrderProducts)
        },

        quantityOrdered() {
            return this.ordered
        },

        activityLogsLink() {
            return `/reports/activity-log?sort=-id&filter[subject_type]=App\\Models\\Product&filter[subject_id]=${this.product.id}`
        },

        orderProductReportLink() {
            return `/reports/order-products?-order_placed_at&filter%5Bproduct_id%5D=${this.product.id}`
        }
    },

    created: function () {
        if (this.expanded) {
            this.toggleProductDetails();
        }
    },

    methods: {
        editProduct() {
            this.$modal.showUpsertProductModal(this.product);
        },

        loadPdfIntoIframe() {
            let data = {
                data: { product_sku: this.product['sku'] },
                template: 'product-labels/' + this.templateSelected,
            };

            this.apiPostPdfPreview(data)
                .then(response => {
                    let blob = new Blob([response.data], { type: 'application/pdf' });
                    this.pdfLabelBlob = URL.createObjectURL(blob);
                })
                .catch(error => {
                    this.displayApiCallError(error);
                });
        },

        showInventoryMovementModal(inventory_id) {
            this.$modal.showRecentInventoryMovementsModal(inventory_id);
        },

        showInventoryReservationsModal(inventory_id) {
            this.$modal.showInventoryReservationsModal(inventory_id);
        },

        movementsReportLink(warehouse_code) {
            return '/reports/inventory-movements?filter[created_at_between]=&filter[warehouse_code]=' + warehouse_code + '&filter[search]=' + this.product['sku']
        },

        soldLast7Days(warehouse_id) {
            const soldLast7DaysArray = this.product['inventoryMovementsStatistics'][warehouse_id];

            if (soldLast7DaysArray === null) {
                return 0;
            }

            return soldLast7DaysArray['quantity_sold']
        },

        updateInventoryLevels(inventory) {
            if (this.currentUser()['warehouse'] && inventory['warehouse_code'] !== this.currentUser()['warehouse']['code']) {
                return;
            }

            const originalRestockLevel = Number(inventory['restock_level']);
            const originalReorderPoint = Number(inventory['reorder_point']);
            const originalQuantityRequired = Number(inventory['quantity_required']);

            this.apiInventoryPost({
                'id': inventory['id'],
                'restock_level': inventory['restock_level'],
                'reorder_point': inventory['reorder_point'],
            }).then(response => {
                if (response.data && response.data.data && response.data.data[0]) {
                    inventory['quantity_required'] = response.data.data[0]['quantity_required'];
                }
            }).catch(error => {
                inventory['quantity_required'] = originalQuantityRequired;
                inventory['restock_level'] = originalRestockLevel;
                inventory['reorder_point'] = originalReorderPoint;
                this.displayApiCallError(error);
            });
        },

        adjustRestockLevel(inventory, delta) {
            if (this.currentUser()['warehouse'] && inventory['warehouse_code'] !== this.currentUser()['warehouse']['code']) {
                return;
            }
            const newValue = Number(inventory['restock_level']) + delta;
            inventory['restock_level'] = Math.max(newValue, Number(inventory['reorder_point']));
            this.updateInventoryLevels(inventory);
        },

        adjustReorderPoint(inventory, delta) {
            if (this.currentUser()['warehouse'] && inventory['warehouse_code'] !== this.currentUser()['warehouse']['code']) {
                return;
            }
            const newValue = Math.max(0, Number(inventory['reorder_point']) + delta);
            inventory['reorder_point'] = newValue;
            if (Number(inventory['restock_level']) < newValue) {
                inventory['restock_level'] = newValue;
            }
            this.updateInventoryLevels(inventory);
        },

        hide() {
            $('#filterConfigurationModal').modal('hide');
        },

        orderProduct(quantity) {
            this.ordered = quantity;
            this.hide();
        },

        sharingAvailable() {
            return navigator.share;
        },

        shareLink() {
            navigator.share({
                url: '/products?search=' + this.product['sku'],
                title: document.title
            });
        },

        openShelfLabelModal() {
            if (!this.product?.inventory) {
                return;
            }

            let inventory = this.product.inventory[this.currentUser()['warehouse_code']];

            if (!inventory) {
                return;
            }

            this.$modal.show('edit-shelf-label-modal', { inventory });
        },

        toggleProductDetails() {
            this.showDetails = !this.showDetails;
            if (this.showDetails) {
                this.loadActiveOrders();
                this.loadCompletedOrders(10);
                this.currentTab = 'inventory';
            }
        },

        loadActiveOrders: function () {
            this.statusMessageOrder = "Loading orders ..."
            const params = {
                'filter[product_id]': this.product['id'],
                'filter[order.is_active]': true,
                'sort': '-order_placed_at',
                'include': 'order',
                'per_page': 999
            }
            this.apiGetOrderProducts(params)
                .then(({ data }) => {
                    this.statusMessageOrder = '';
                    this.activeOrderProducts = data.data;
                })
                .catch((error) => {
                    this.displayApiCallError(error);
                });
        },

        loadCompletedOrders: function (count = 5) {
            const params = {
                'filter[product_id]': this.product['id'],
                'filter[order.is_active]': false,
                'sort': '-order_placed_at',
                'include': 'order',
                'per_page': count
            }
            this.apiGetOrderProducts(params)
                .then(({ data }) => {
                    this.statusMessageOrder = '';
                    this.completeOrderProducts = data.data;
                })
                .catch((error) => {
                    this.displayApiCallError(error);
                });
        },

        loadActivityLog: function () {
            this.statusMessageActivity = this.$t('Loading activities ...');
            const params = {
                'filter[subject_type]': 'App\\Models\\Product',
                'filter[subject_id]': this.product['id'],
                'sort': '-id',
                'include': 'causer',
                'per_page': 100
            }

            this.apiGetActivityLog(params)
                .then(({ data }) => {
                    this.statusMessageActivity = '';
                    this.activityLog = data.data
                    if (this.activityLog.length === 0) {
                        this.statusMessageActivity = this.$t('No activities found');
                    }
                })
                .catch((error) => {
                    this.displayApiCallError(error);
                });
        },

        dashIfZero(value) {
            return value === 0 ? '-' : value;
        },

        getProductLink(orderProduct) {
            return '/orders?search=' + orderProduct['order']['order_number'];
        },

        getProductQuantity(orderProduct) {
            return orderProduct['product'] ? Number(orderProduct['product']['quantity']) : -1;
        },

        ifHasEnoughStock(orderProduct) {
            return this.getProductQuantity(orderProduct) < Number(orderProduct['quantity_ordered']);
        },

        addAliasToProduct(barcode) {
            const params = {
                'product_id': this.product['id'],
                'alias': barcode
            };

            this.apiPostProductsAliases(params)
                .then((response) => {
                    this.product.aliases.push(response.data.data);
                    document.getElementById('newProductAliasInput-'+this.product['id']).value = barcode;
                    this.setFocusElementById('newProductAliasInput-'+this.product['id']);
                })
                .catch((error) => {
                    this.displayApiCallError(error);
                });
            return null;
        },

        addAssemblyProduct(barcode) {
            const params = {
                'product_id': this.product['id'],
                'sku': barcode
            };

            this.apiPostAssemblyProductElement(params)
                .then((response) => {
                    this.product['assemblyProducts'].push(response.data.data);
                    document.getElementById('assembly-input-'+this.product['id']).value = barcode;
                    this.setFocusElementById('assembly-input-'+this.product['id']);
                })
                .catch((error) => {
                    this.displayApiCallError(error);
                });

            return null;
        },

        updateAssemblyProductElementQuantity(product) {
            const params = {
                'quantity': product.required_quantity,
            };

            this.apiPutAssemblyProductElement(product.id, params)
                .then(() => {
                    this.$snotify.success(this.$t('Assembly product element required quantity updated successfully.'));
                })
                .catch((error) => {
                    this.displayApiCallError(error);
                });
        },

        removeAssemblyProduct(elementId) {
            this.apiDeleteAssemblyProductElement(elementId)
                .then(() => {
                    const index = this.product.assemblyProducts.findIndex(product => product.id === elementId);
                    if (index > -1) {
                        this.product.assemblyProducts.splice(index, 1);
                    }
                    this.$snotify.success(this.$t('Assembly product element removed successfully.'));
                })
                .catch((error) => {
                    this.displayApiCallError(error);
                });
        },

        showAssembleProductQuantityModal() {
            this.$modal.showAssembleProductQuantityModal(this.product.id);
        },

        showDisassembleProductQuantityModal() {
            this.$modal.showAssembleProductQuantityModal(this.product.id, true);
        },

        changeQuantityAlias(alias) {
            this.apiPutProductsAliases(alias.id, { quantity: alias.quantity })
                .then(() => {
                })
                .catch((error) => {
                    this.displayApiCallError(error);
                });
            return null;
        },

        deleteAlias(alias) {
            this.$snotify.confirm(this.$t('Once deleted, data cannot be restored'), this.$t('Are you sure?'), {
                position: 'centerCenter',
                buttons: [
                    {
                        text: this.$t('Yes'),
                        action: (toast) => {
                            this.apiDeleteProductsAliases(alias.id, { quantity: alias.quantity })
                                .then(() => {
                                    const index = this.product.aliases.indexOf(alias);
                                    if (index > -1) {
                                        this.product.aliases.splice(index, 1);
                                    }
                                })
                                .catch((error) => {
                                    this.displayApiCallError(error);
                                });
                            this.$snotify.remove(toast.id);
                        }
                    },
                    { text: this.$t('Cancel') },
                ]
            });
        },

        changeProductWeightSize(product) {
            this.apiPutProducts(product.id, {
                weight: product.weight,
                length: product.length,
                width: product.width,
                height: product.height,
                pack_quantity: product.pack_quantity,
            })
            .catch((error) => {
                this.displayApiCallError(error);
            });
        }
    }
}
</script>

<style scoped>
li {
    margin-right: 5px;
}

.badge {
    font-family: "Lato", -apple-system, system-ui, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", sans-serif;
}

.table th,
.table td {
    padding: 0.0rem;
}

.btn:active {
    background-color: rgb(94, 79, 126);
    border-color: rgb(94, 79, 126);
    box-shadow: 0 1px 1px rgba(255, 255, 255, 0.075) inset, 0 0 8px rgba(114, 96, 153, 0.6);
    outline: 0 none;
}

.newProductAliasInput {
    font-size: 8pt;
}

.quantityPackAliasInput {
    font-size: 8pt;
}

.form-small {
    font-size: 8pt;
}

.number-spinner {
    display: inline-flex;
    align-items: center;
}

.number-spinner input {
    width: 55px;
    padding-left: 2px;
    padding-right: 2px;
}

.number-spinner .btn {
    line-height: 1;
}
</style>
