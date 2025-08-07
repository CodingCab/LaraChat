<template>
    <div>
        <div class="card card-default">
            <div class="card-header">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span>
                        {{ $t('Point of Sale - Configuration') }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="w-100" style="height: 100px;" ref="loadingContainer" v-if="isLoading"></div>
                <div v-else>
                    <p>{{ $t('Set the necessary data for the proper functioning of the Point of Sale') }}</p>
                    <ValidationObserver ref="form">
                        <form class="form" @submit.prevent="saveChanged">
                            <div class="form-group">
                                <label class="form-label"
                                       for="next_transaction_number">{{ $t('Next Transaction Number') }}</label>
                                <ValidationProvider vid="next_transaction_number" name="next_transaction_number"
                                                    v-slot="{ errors }">
                                    <input v-model="configuration.next_transaction_number"
                                           id="next_transaction_number" type="number" step="1" min="0"
                                           class="form-control" required :class="{'is-invalid': errors.length > 0}">
                                    <div class="invalid-feedback">
                                        {{ errors[0] }}
                                    </div>
                                </ValidationProvider>
                            </div>
                            <div class="text-right">
                                <b-button dusk="saveButton" variant="primary" class="btn btn-primary">{{ $t('Save') }}</b-button>
                            </div>
                        </form>
                    </ValidationObserver>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import api from "../../../mixins/api.vue";
import helpers from "../../../mixins/helpers";
import {ValidationObserver, ValidationProvider} from "vee-validate";
import Loading from '../../../mixins/loading-overlay';

export default {
    mixins: [api, helpers, Loading],

    components: {
        ValidationObserver,
        ValidationProvider
    },

    mounted() {
        this.loadConfiguration();
    },

    data() {
        return {
            configuration: {}
        }
    },

    methods: {
        loadConfiguration() {
            this.showLoading();

            this.apiGetPointOfSaleConfiguration()
                .then(({data}) => {
                    this.configuration = data.data;
                    this.hideLoading();
                })
                .catch(e => {
                    this.displayApiCallError(e);
                    this.hideLoading();
                });
        },

        saveChanged() {
            this.showLoading();
            let data = {
                next_transaction_number: this.configuration.next_transaction_number
            };
            this.apiPostPointOfSaleConfiguration(this.configuration.id, data)
                .then(() => {
                    this.$snotify.success(this.$t('Configuration saved successfully.'));
                })
                .catch((error) => {
                    this.displayApiCallError(error);
                    this.hideLoading();
                })
                .finally(this.hideLoading);
        }
    },
}
</script>
