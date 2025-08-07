<template>
    <div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div ref="loadingContainer2" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $t('Edit Magento Api Connection') }}</h5>
                </div>
                <div class="modal-body">
                    <ValidationObserver ref="form">
                        <form class="form" @submit.prevent="submit" ref="loadingContainer">

                            <div class="form-group">
                                <label class="form-label" for="base_url">{{ $t('Base URL') }}</label>
                                <ValidationProvider vid="base_url" name="base_url" v-slot="{ errors }">
                                    <input v-model="config.base_url" :class="{
                                            'form-control': true,
                                            'is-invalid': errors.length > 0,
                                        }" id="create-base_url" type="url" required>
                                    <div class="invalid-feedback">
                                        {{ errors[0] }}
                                    </div>
                                </ValidationProvider>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="magento_store_id">{{ $t('Store ID') }}</label>
                                <ValidationProvider vid="magento_store_id" name="magento_store_id" v-slot="{ errors }">
                                    <input v-model="config.magento_store_id" :class="{
                                        'form-control': true,
                                        'is-invalid': errors.length > 0,
                                    }" id="create-magento_store_id" type="number" required>
                                    <div class="invalid-feedback">
                                        {{ errors[0] }}
                                    </div>
                                </ValidationProvider>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="tag">{{ $t('Inventory source warehouse tag') }}</label>
                                <ValidationProvider vid="tag" name="tag" v-slot="{ errors }">
                                    <input v-model="config.tag" :class="{
                                        'form-control': true,
                                        'is-invalid': errors.length > 0,
                                    }" id="create-tag" required>
                                    <div class="invalid-feedback">
                                        {{ errors[0] }}
                                    </div>
                                </ValidationProvider>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="pricing_source_warehouse_id">{{ $t('Pricing source warehouse') }}</label>
                                <ValidationProvider vid="pricing_source_warehouse_id" name="pricing_source_warehouse_id" v-slot="{ errors }">
                                    <select v-model="config.pricing_source_warehouse_id" :class="{
                                        'form-control': true,
                                        'is-invalid': errors.length > 0,
                                    }" id="create-pricing_source_warehouse_id" required>
                                        <option v-for="warehouse in warehouses"
                                                :value="warehouse.id" :key="warehouse.id"
                                        >
                                            {{ warehouse.name }}
                                        </option>
                                    </select>
                                    <div class="invalid-feedback">
                                        {{ errors[0] }}
                                    </div>
                                </ValidationProvider>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="api_access_token">{{ $t('Access Token') }}</label>
                                <ValidationProvider vid="api_access_token" name="api_access_token" v-slot="{ errors }">
                                    <input v-model="config.api_access_token" :class="{
                                        'form-control': true,
                                        'is-invalid': errors.length > 0,
                                    }" id="create-api_access_token" required type="password">
                                    <div class="invalid-feedback">
                                        {{ errors[0] }}
                                    </div>
                                </ValidationProvider>
                            </div>
                        </form>
                    </ValidationObserver>
                </div>
                <div class="modal-footer" style="justify-content:space-between">
                    <button type="button" @click.prevent="confirmDelete" class="btn btn-outline-danger float-left">{{ $t('Delete') }}</button>
                    <div>
                        <button type="button" @click="closeModal" class="btn btn-secondary">{{ $t('Cancel') }}</button>
                        <button type="button" @click="submit" class="btn btn-primary">{{ $t('Save') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ValidationObserver, ValidationProvider } from "vee-validate";

import Loading from "../../../mixins/loading-overlay";
import api from "../../../mixins/api";

export default {
    name: "EditModal",

    mixins: [api, Loading],

    components: {
        ValidationObserver, ValidationProvider
    },

    mounted() {
        this.fetchWarehouses()
    },

    data() {
        return {
            config: {
                base_url: location.protocol + '//' + location.host
            },
            warehouses: []
        }
    },

    props: {
        connection: Object,
    },

    watch: {
        connection: function(newVal) {
            this.config = {
                base_url: newVal.base_url,
                magento_store_id: newVal.magento_store_id,
                tag: newVal.tags.length ? newVal.tags[0].name : '',
                pricing_source_warehouse_id: newVal.pricing_source_warehouse_id,
                api_access_token: newVal.api_access_token
            };
        }
    },

    methods: {

        fetchWarehouses: function () {
            this.apiGetWarehouses({
                'per_page': 100,
                'sort': 'code',
                'include': 'tags'
            })
                .then(({data}) => {
                    this.warehouses = data.data;
                })
        },

        submit() {
            this.showLoading();
            this.apiPutMagentoApiConnection(this.connection.id, this.config)
                .then(({ data }) => {
                    this.closeModal();
                    this.$emit('onUpdated', data.data);
                })
                .catch((error) => {
                    this.displayApiCallError(error);
                    if (error.response) {
                        if (error.response.status === 422) {
                            this.$refs.form.setErrors(error.response.data.errors);
                        }
                    }
                })
                .finally(this.hideLoading);
        },

        closeModal() {
            $(this.$el).modal('hide');
        },

        confirmDelete() {
            this.$snotify.confirm(this.$t('Once deleted, data cannot be restored'), this.$t('Are you sure?'), {
                position: 'centerCenter',
                buttons: [
                    {
                        text: this.$t('Yes'),
                        action: (toast) => {
                            this.apiDeleteMagentoApiConnection(this.connection.id)
                                .then(() => {
                                    this.closeModal();
                                    this.$emit('onUpdated');
                                })
                                .catch(() => {
                                    this.$snotify.error(this.$t('Error occurred while deleting.'));
                                });
                            this.$snotify.remove(toast.id);
                        }
                    },
                    {text: this.$t('Cancel')},
                ]
            });
        },
    },
}
</script>
