<template>
    <ValidationObserver ref="form">
        <form class="form" @submit.prevent="submit" ref="loadingContainer">
            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="locationID">{{ $t('Warehouse Code') }}</label>
                <div class="col-sm-9">
                    <ValidationProvider vid="location_id" name="Location ID" v-slot="{ errors }">
                        <input v-model="location_id" :class="{
                            'form-control': true,
                            'is-invalid': errors.length > 0,
                        }" id="locationID" :placeholder="$t('Warehouse Code')" required>
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
                <label class="col-sm-3 col-form-label" for="username">{{ $t('Username') }}</label>
                <div class="col-sm-9">
                    <ValidationProvider vid="username" name="username" v-slot="{ errors }">
                        <input v-model="username" :class="{
                            'form-control': true,
                            'is-invalid': errors.length > 0,
                        }" id="username" :placeholder="$t('Username')" required>
                        <div class="invalid-feedback">
                            {{ errors[0] }}
                        </div>
                    </ValidationProvider>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="password">{{ $t('Password') }}</label>
                <div class="col-sm-9">
                    <ValidationProvider vid="password" name="password" v-slot="{ errors }">
                        <input v-model="password" type="password" :class="{
                            'form-control': true,
                            'is-invalid': errors.length > 0,
                        }" id="password" :placeholder="$t('Password')" required>
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
        username: null,
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
                password: this.password,
                username: this.username,
            };
            this.apiPostRmsapiConnections(data)
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
            this.password = null;
            this.username = null;
        }
    }
}
</script>
