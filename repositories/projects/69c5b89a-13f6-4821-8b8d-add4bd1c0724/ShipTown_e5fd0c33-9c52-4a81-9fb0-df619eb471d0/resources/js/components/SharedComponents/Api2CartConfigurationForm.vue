<template>
    <ValidationObserver ref="form">
        <form class="form" @submit.prevent="submit" ref="loadingContainer">
            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="locationID">{{ $t('Location ID') }}</label>
                <div class="col-sm-9">
                    <ValidationProvider vid="location_id" name="Location ID" v-slot="{ errors }">
                        <input v-model="location_id" :class="{
                            'form-control': true,
                            'is-invalid': errors.length > 0,
                        }" id="locationID" :placeholder="$t('Location ID')" required>
                        <div class="invalid-feedback">
                            {{ errors[0] }}
                        </div>
                    </ValidationProvider>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="url">{{ $t('URL') }}</label>
                <div class="col-sm-9">
                    <ValidationProvider vid="url" name="url" v-slot="{ errors }">
                        <input type="url" v-model="url" :class="{
                            'form-control': true,
                            'is-invalid': errors.length > 0,
                        }" id="url" :placeholder="$t('URL')" required>
                        <div class="invalid-feedback">
                            {{ errors[0] }}
                        </div>
                    </ValidationProvider>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="type">{{ $t('Store Type') }}</label>
                <div class="col-sm-9">
                    <ValidationProvider vid="type" name="type" v-slot="{ errors }">
                        <select v-model="type" :class="{
                            'form-control': true,
                            'is-invalid': errors.length > 0,
                        }" id="type" :placeholder="$t('Select')" required>
                            <option value="magento">Magento</option>
                            <option value="prestashop">Prestashop</option>
                            <option value="shopify">Shopify</option>
                        </select>
                        <div class="invalid-feedback">
                            {{ errors[0] }}
                        </div>
                    </ValidationProvider>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="bridge_api_key">{{ $t('API Key') }}</label>
                <div class="col-sm-9">
                    <ValidationProvider vid="bridge_api_key" name="bridge_api_key" v-slot="{ errors }">
                        <input v-model="bridge_api_key" :class="{
                            'form-control': true,
                            'is-invalid': errors.length > 0,
                        }" id="bridge_api_key" :placeholder="$t('API Key')" required>
                        <div class="invalid-feedback">
                            {{ errors[0] }}
                        </div>
                    </ValidationProvider>
                </div>
            </div>
        </form>
    </ValidationObserver>
</template>

<script>
    import { ValidationObserver, ValidationProvider } from 'vee-validate';

    import Loading from '../../mixins/loading-overlay';
    import api from "../../mixins/api";

    export default {
        components: {
            ValidationObserver, ValidationProvider
        },

        mixins: [api, Loading],

        data: () => ({
            location_id: null,
            url: null,
            password: null,
            bridge_api_key: null,
            type: null,
        }),

        props: {
            id: Number,
        },

        methods: {
            submit() {
                this.showLoading();
                let data = {
                    location_id: this.location_id,
                    url: this.url,
                    type: this.type,
                    bridge_api_key: this.bridge_api_key,
                };
                this.apiPostApi2cartConnection(data)
                    .then(({ data }) => {
                        const config = data.data;

                        this.$emit('saved', config);
                         // this.reset();
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

            reset() {
                this.location_id = null;
                this.url = null;
                this.type = null;
                this.bridge_api_key = null;
            }
        }
    }
</script>
