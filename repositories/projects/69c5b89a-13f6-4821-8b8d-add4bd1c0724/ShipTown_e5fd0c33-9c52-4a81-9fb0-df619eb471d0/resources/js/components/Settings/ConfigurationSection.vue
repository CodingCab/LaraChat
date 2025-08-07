<template>
    <div>

        <div class="card card-default">
            <div class="card-header">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span>
                        {{ $t('Base Configuration') }}
                    </span>
                </div>
            </div>

            <div class="card-body" v-if="configuration !== null">
                <ValidationObserver ref="form">
                    <form class="form" @submit.prevent="submit" ref="loadingContainer">
                        <div class="form-group">
                            <label for="business_name">{{ $t('Business Name') }}</label>
                            <ValidationProvider vid="business_name" name="business_name" v-slot="{ errors }">
                                <input v-model="configuration.business_name" :class="{
                                    'form-control': true,
                                    'is-invalid': errors.length > 0,
                                }" id="business_name" required>
                                <div class="invalid-feedback">
                                    {{ errors[0] }}
                                </div>
                            </ValidationProvider>
                        </div>

                        <button class="btn btn-primary" :disabled="!btnSave" @click.prevent="updateConfiguration">{{ $t('Save') }}</button>
                    </form>
                </ValidationObserver>
            </div>
        </div>

    </div>
</template>

<script>
import { ValidationObserver, ValidationProvider } from "vee-validate";

import api from "../../mixins/api";

export default {
    mixins: [api],

    components: {
        ValidationObserver, ValidationProvider
    },

    name: "ConfigurationSection",

    data: function () {
        return {
            btnSave: true,
            configuration: {}
        }
    },

    created() {
        this.apiGetConfiguration()
            .then(({ data }) => {
                this.configuration = data.data;
            });
    },

    methods: {
        updateConfiguration() {
            this.btnSave = false;
            this.apiSaveConfiguration(this.configuration)
                .then(() => {
                    this.$snotify.success(this.$t('Configuration updated'));
                })
                .catch(() => {
                    this.$snotify.error(this.$t('Update configuration failed'));
                })
                .finally(() => {
                    this.btnSave = true
                });
        },
    }
}
</script>

<style scoped>

</style>
