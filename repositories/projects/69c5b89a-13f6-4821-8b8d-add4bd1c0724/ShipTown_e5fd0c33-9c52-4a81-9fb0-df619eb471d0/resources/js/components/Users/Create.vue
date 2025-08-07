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
                        }" id="name" placeholder="John Doe" required>
                        <div class="invalid-feedback">
                            {{ errors[0] }}
                        </div>
                    </ValidationProvider>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="create-email">{{ $t('Email') }}</label>
                <div class="col-sm-9">
                    <ValidationProvider vid="email" name="email" v-slot="{ errors }">
                        <input type="email" v-model="email" :class="{
                            'form-control': true,
                            'is-invalid': errors.length > 0,
                        }" id="create-email" placeholder="foo@bar.com" required>
                        <div class="invalid-feedback">
                            {{ errors[0] }}
                        </div>
                    </ValidationProvider>
                </div>
            </div>
            <div class="form-group row">
                <label for="role_id" class="col-sm-3 col-form-label">{{ $t('Role') }}</label>
                <div class="col-sm-9">
                    <ValidationProvider vid="role_id" name="role_id" v-slot="{ errors }">
                        <select v-model="roleId" :class="{
                                'form-control': true,
                                'is-invalid': errors.length > 0,
                            }"
                            id="role_id" required>
                            <option value=""></option>
                            <option v-for="(role, i) in roles" :key="i" :value="role.id">
                                {{ role.name }}
                            </option>
                        </select>
                        <div class="invalid-feedback">
                            {{ errors[0] }}
                        </div>
                    </ValidationProvider>
                </div>
            </div>
            <div class="form-group row">
                <label for="create-warehouse_id" class="col-sm-3 col-form-label">{{ $t('Warehouse') }}</label>
                <div class="col-sm-9">
                    <ValidationProvider vid="warehouse_id" name="warehouse_id" v-slot="{ errors }">
                        <select v-model="warehouse" :class="{
                                'form-control': true,
                                'is-invalid': errors.length > 0,
                            }"
                            id="create-warehouse_id" required>
                            <option value=""></option>
                            <option v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse">
                                {{ warehouse.name }}
                            </option>
                        </select>
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

    props: {
        roles: Array,
        warehouses: Array
    },

    mixins: [api, Loading],

    data: () => ({
        name: null,
        email: null,
        roleId: null,
        warehouse: null,
    }),

    methods: {
        submit() {
            this.showLoading();
            let data = {
                ask_for_shipping_number: 0,
                name: this.name,
                email: this.email,
                role_id: this.roleId,
                warehouse_id: this.warehouse?.id,
                warehouse_code: this.warehouse?.code,
            };
            this.apiPostUserStore(data)
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
