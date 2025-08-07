<template>
    <ValidationObserver ref="form">
        <form class="form" @submit.prevent="submit" ref="loadingContainer">
            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="name">{{ $t('Name') }}</label>
                <div class="col-sm-9">
                    <ValidationProvider vid="name" name="name" v-slot="{ errors }">
                        <input v-model="name" :class="{
                            'form-control': true,
                            'is-invalid': errors.length > 0,
                        }" id="name" placeholder="Cashier" required>
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
        name: null
    }),

    methods: {
        submit() {
            this.showLoading();
            this.apiPostUserRole({
                name: this.name
            })
                .then(({ data }) => {
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
        }
    }
}
</script>
