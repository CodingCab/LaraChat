<template>
    <ValidationObserver ref="form">
        <form class="form" @submit.prevent="submit" ref="loadingContainer">
            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="email">{{ $t('Email') }}</label>
                <div class="col-sm-9">
                    <ValidationProvider vid="email" name="email" v-slot="{ errors }">
                        <input type="email" v-model="email" :class="{
                            'form-control': true,
                            'is-invalid': errors.length > 0,
                        }" id="email" placeholder="foo@bar.com" required>
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
        email: null
    }),

    methods: {

        submit() {
            this.showLoading();
            let data = {
                email: this.email,
            };
            this.apiPostUserInvite(data)
                .then(({ data }) => {
                    this.$emit('saved');
                    this.$snotify.success(this.$t('User invite sent.'));
                    this.reset();
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
            this.email = null;
        }
    }
}
</script>
