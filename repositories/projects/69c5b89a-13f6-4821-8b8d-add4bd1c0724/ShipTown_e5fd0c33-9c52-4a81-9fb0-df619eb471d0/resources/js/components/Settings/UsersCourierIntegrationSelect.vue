<template>
    <div>
        <div class="card card-default">
            <div class="card-header">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span>
                        {{ $t('Shipping Labels Configuration') }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 align-middle">{{ $t('If courier label is not specified on order, after last item is packed, automatically print this courier label') }}</div>
                    <div class="col-12 mt-1 text-right" style="width: 200px;">
                        <select class="form-control" @change="updateUsersAddressLabelTemplate" v-model="selected_address_label_template">
                            <option value=""></option>
                            <template v-for="shipping_service in shipping_services">
                                <option :value="shipping_service.code" :key="shipping_service.id" >{{ shipping_service['code'] }}</option>
                            </template>
                        </select>
                    </div>
                </div>
                <div class="row mt-4">
                    <div class="col-10">{{ $t('Ask to scan shipping number') }}</div>
                    <div class="col-2 text-right">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" @change="saveAskForShippingNumberValue" class="custom-control-input" id="swicth-scan-shipping-number" v-model="selected_ask_for_shipping_number">
                            <label class="custom-control-label" for="swicth-scan-shipping-number"></label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import api from "../../mixins/api";
import Vue from "vue";
import helpers from "../../mixins/helpers";

export default {
    mixins: [api, helpers],

    mounted() {
        this.loadShippingServices();
        this.selected_address_label_template = Vue.prototype.$currentUser.address_label_template;
        this.selected_ask_for_shipping_number = Vue.prototype.$currentUser.ask_for_shipping_number;
    },

    data: () => ({
        shipping_services: [],
        selected_address_label_template: "",
        selected_ask_for_shipping_number: false,
    }),

    methods: {
        updateUsersAddressLabelTemplate() {
            this.apiPostUserMe({
                    'address_label_template': this.selected_address_label_template
                })
                .catch(e => {
                    this.displayApiCallError(e);
                });

            this.loadShippingServices();
        },

        saveAskForShippingNumberValue() {
            this.apiPostUserMe({
                    'ask_for_shipping_number': this.selected_ask_for_shipping_number
                })
                .catch(e => {
                    this.displayApiCallError(e);
                });
        },

        loadShippingServices()  {
            this.apiGetShippingServices()
                .then(({ data }) => {
                    this.shipping_services = data.data;
                })
                .catch(e => {
                    this.displayApiCallError(e);
                });
        }
    },
}
</script>
