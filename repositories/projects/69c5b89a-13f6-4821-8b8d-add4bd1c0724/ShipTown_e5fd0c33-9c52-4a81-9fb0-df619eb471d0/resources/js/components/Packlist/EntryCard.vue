<template>
    <div class="row">
        <div class="col-lg-6 text-left">
            <product-info-card v-if="entry['product']" :product="entry['product']" :sku_orderd="entry['sku_ordered']" :name_ordered="entry['name_ordered']" :show-product-descriptions="false" />
            <div v-else>
                <div class="row">
                    <div class="col-12">
                            <div class="text-primary h5">{{ entry['name_ordered'] }}</div>
                            <div>
                                {{ $tc('sku ordered') }}:
                                <font-awesome-icon icon="copy" class="fa-xs btn-link" role="button" @click="copyToClipBoard((product ? entry['name_ordered'] : ''))"></font-awesome-icon>
                                <strong class="bg-warning">&nbsp;{{ entry['sku_ordered'] }}</strong>
                            </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="row">
                <div class="col-6 small">
                    <div>{{ $t('sku ordered') }}: <b :class="entry['product_id'] ? '' : 'bg-warning'">{{ entry['sku_ordered'] }}</b></div>
                    <div >
                        {{ $t('ordered') }}: <b>{{ dashIfZero(Number(entry['quantity_ordered'])) }}</b>
                    </div>
                    <div >
                        {{ $t('price') }}: <b>{{ dashIfZero(Number(entry['price'])) }}</b>
                    </div>
                    <div class="bg-warning" v-if="Number(entry['quantity_split']) > 0">
                        {{ $t('split') }}: <b>{{ dashIfZero(Number(entry['quantity_split'])) }}</b>
                    </div>
                    <div>
                        {{ $t('picked') }}: <b>{{ dashIfZero(Number(entry['quantity_picked'])) }}</b>
                    </div>
                    <div>
                        {{ $t('shipped') }}: <b>{{ dashIfZero(Number(entry['quantity_shipped'])) }}</b>
                    </div>
                    <div v-bind:class="{ 'bg-warning': Number(entry['inventory_source_quantity']) <= 0 }">
                        {{ $t('inventory') }}: <b>{{ dashIfZero(Number(entry['inventory_source_quantity'])) }}</b>
                    </div>
                </div>
                <div class="col-3 text-center" v-bind:class="{ 'bg-warning': Number(entry['quantity_ordered']) !== 1 }">
                    <small>{{ $t('to ship') }}</small>
                    <h3>{{ dashIfZero(Number(entry['quantity_to_ship'])) }}</h3>
                </div>
                <div class="col-3 text-center">
                    <small>{{ $t('shelf') }}</small>
                    <h3>{{ entry['inventory_source_shelf_location'] }}</h3>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import ProductSkuButton from "../SharedComponents/ProductSkuButton.vue";
    import helpers from "../../mixins/helpers";

    export default {
        name: "EntryCard",
        components: {ProductSkuButton},
        mixins: [helpers],

        props: {
            entry: Object,
        },
        computed: {
            productSku() {
               return this.entry['product'] ? this.entry['product']['sku'] : '';
            },
            productUrl() {
                return '/products?filter[sku]=' + this.productSku;
            }
        },
        methods: {
            showProductDetailsModal() {
                this.$modal.showProductDetailsModal(this.entry['product']['id']);
            },

            dashIfZero(value) {
                return value === 0 ? '-' : value;
            },
        }
    }
</script>

<style scoped>

</style>
