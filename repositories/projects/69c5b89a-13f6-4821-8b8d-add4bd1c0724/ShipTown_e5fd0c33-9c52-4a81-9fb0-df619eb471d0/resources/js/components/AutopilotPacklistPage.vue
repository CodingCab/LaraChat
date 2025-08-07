<template>
    <div>
        <template v-if="order">
            <packsheet-page @orderCompleted="nextOrder" :key="'order_id_' + order.id" :order_id="order.id" :previous_order_id="previous_order_id"></packsheet-page>
        </template>

        <div v-else-if="currentStep === 'select'">
            <div class="row col text-center mt-3">
                <modal-button :href="'?step=manual_order_scan'">{{ $t('Scan order manually') }}</modal-button>
                <hr>
                <div v-for="bookmark in bookmarks" class="col-12 mt-1">
                    <a dusk="startAutopilotButton" type="button" class="btn btn-primary col" :href="bookmark['url']">{{ bookmark['name'] }}</a>
                </div>
            </div>
        </div>

        <template v-else-if="currentStep === 'manual_order_scan'">
            <barcode-input-field :input_id="'barcode-input-field'" :url_param_name="'filter[order.order_number]'" @barcodeScanned="openPacksheet" :placeholder="$t('Scan order to start packing')"></barcode-input-field>
        </template>

        <div v-if="finished && !isLoading" class="m-auto text-center">
            <br>
            {{ $t("You've finished packing all orders!") }}<br>
            <span class="small">{{ $t('There are no more orders to pack with specified filters') }}</span>
        </div>
    </div>
</template>

<script>
    import url from "../mixins/url";
    import api from "../mixins/api";
    import beep from "../mixins/beep";
    import Vue from "vue";
    import helpers from "../mixins/helpers";
    import loadingOverlay from "../mixins/loading-overlay";
    import ModalButton from "./Reports/ModalButton.vue";

    export default {
        components: {ModalButton},
        mixins: [loadingOverlay, api, beep, url, helpers],

        data: function() {
            return {
                currentStep: this.getUrlParameter('step'),
                finished: false,
                order: null,
                order_id: null,
                previous_order_id: null,
                bookmarks: [],
            };
        },

        watch: {
            '$route'(to, from) {
                this.currentStep = this.getUrlParameter('step');

                if (!order) {
                    this.setFocusElementById('barcode-input-field', false, true, 300);
                    this.finished = false;
                    this.loadNextOrder();
                }
            }
        },

        mounted() {
            this.apiGetNavigationMenu({
                    'filter[group]': 'packlist',
                    'per_page': 100,
                })
                .then((response) => {
                    this.bookmarks = response.data.data;
                })
                .catch((error) => {
                    this.displayApiCallError(error);
                });

            if (this.getUrlParameter('step', '') === '') {
                this.loadNextOrder();
            }
        },

        methods: {
            nextOrder() {
                this.previous_order_id = this.order ? this.order['id'] : null;
                this.order = null;

                if (this.currentStep === 'manual_order_scan') {
                    this.setUrlParameter('filter[order.order_number]', '');
                    return;
                }

                this.loadNextOrder();
            },

            openPacksheet(orderNumber) {
                if (orderNumber === '') {
                    return;
                }

                let params = {
                    'filter[order_number]': orderNumber,
                    'filter[is_active]': true,
                    'per_page': 1,
                };

                this.apiGetOrders(params)
                    .then((response) => {
                        if (response.data.data.length === 0) {
                            this.notifyError(this.$t('Order not found or already closed') + ': ' + orderNumber);
                            return;
                        }

                        window.open('/order/packsheet/' + response.data.data[0].id + '?auto_print_courier_label=false&sort=sku_ordered,inventory_source_shelf_location,product.department,product.category', '_blank');
                    })
                    .catch((error) => {
                        this.displayApiCallError(error);
                    });
            },

            loadNextOrder() {
                if(! Vue.prototype.$currentUser['warehouse_id']) {
                    this.notifyError(this.$t('User does not have warehouse assigned! Please assign in Settings->User'));
                    return;
                }

                this.showLoading();

                let params = {
                    'filter[inventory_source_warehouse_id]': this.getUrlParameter('filter[inventory_source_warehouse_id]', Vue.prototype.$currentUser['warehouse_id']),
                    'filter[status]': this.getUrlParameter('status'),
                    'sort': this.getUrlParameter('sort', 'order_placed_at'),
                    'per_page': this.getUrlParameter('per_page', 1),
                    'filter[order_number]': this.getUrlParameter('filter[order.order_number]'),
                };

                this.apiGetPacklistOrder(params)
                    .then((response) => {
                        this.order = response.data.data[0];
                        this.finished = false;
                        this.hideLoading();
                    })
                    .catch((error) => {
                        if (this.currentStep == 'manual_order_scan') {
                            this.notifyError(this.$t('This order has been packed already') + ': ' + this.getUrlParameter('filter[order.order_number]'));;
                            return;
                        }

                        this.displayApiCallError(error);
                        if (error.response.status === 404) {
                            this.finished = true;
                        }
                    })
                    .finally(() => {
                        this.hideLoading();
                        this.setFocusElementById('barcode-input-field', false, true, 300);
                    })
            },
        },
    }
</script>


<style lang="scss">

</style>
