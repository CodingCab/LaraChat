<template>
    <b-modal id="modal-create-connection"
        title="Add Connection"
        ok-title="Save"
        @ok="submit"
    >
        <ValidationObserver ref="form">
            <form class="form" @submit.prevent="submit" ref="loadingContainer">
                <div class="form-group">
                    <label class="form-label" for="base_url">{{ $t('Base URL') }}</label>
                    <input v-model="config.base_url" class="form-control" id="create-base_url" type="url" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="magento_store_id">{{ $t('Store ID') }}</label>
                    <input v-model="config.magento_store_id" class="form-control" id="create-magento_store_id" type="number" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="tag">{{ $t('Inventory source warehouse tag') }}</label>
                    <input v-model="config.tag" class="form-control" id="create-tag" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="pricing_source_warehouse_id">{{ $t('Pricing source warehouse') }}</label>
                    <select v-model="config.pricing_source_warehouse_id" class="form-control" id="create-pricing_source_warehouse_id" required>
                        <option v-for="warehouse in warehouses" :value="warehouse.id" :key="warehouse.id">
                            {{ warehouse.name }}
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="api_access_token">{{ $t('Access Token') }}</label>
                    <input v-model="config.api_access_token" class="form-control" id="api_access_token" type="password" required>
                </div>
            </form>
        </ValidationObserver>

        <template #modal-footer>
            <b-button
                variant="secondary"
                class="float-right"
                @click="closeModal"
            >
                {{ $t('Cancel') }}
            </b-button>
            <b-button @click="submit" variant="primary" class="float-right">{{ $t('Save') }}</b-button>
        </template>
    </b-modal>
</template>

<script>
import { ValidationObserver, ValidationProvider } from "vee-validate";

import Loading from "../../../mixins/loading-overlay";
import api from "../../../mixins/api";

export default {
    name: "CreateModal",

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
                base_url: ''
            },
            warehouses: []
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
            this.apiPostMagentoApiConnection({...this.config})
                .then(({ data }) => {
                    this.$snotify.success(this.$t('Connection created.'));
                    this.resetForm()
                    this.closeModal()
                    this.$emit('onCreated');
                })
                .catch((error) => {
                    if (error.response) {
                        if (error.response.status === 422) {
                            this.$refs.form.setErrors(error.response.data.errors);
                        }
                    }
                })
                .finally(this.hideLoading);
        },

        resetForm(){
            this.config = {}
        },

        closeModal() {
            this.$bvModal.hide('modal-create-connection')
        }
    },
}
</script>
