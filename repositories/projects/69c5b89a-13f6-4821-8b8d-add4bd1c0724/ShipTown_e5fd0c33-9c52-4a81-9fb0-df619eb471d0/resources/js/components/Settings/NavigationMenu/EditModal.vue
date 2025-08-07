<template>
    <div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div ref="loadingContainer2" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $t('Edit Navigation Menu') }}</h5>
                </div>
                <div class="modal-body">
                    <ValidationObserver ref="form">
                        <form class="form" @submit.prevent="submit" ref="loadingContainer">
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label" for="edit-name">{{ $t('Name') }}</label>
                                <div class="col-sm-9">
                                    <ValidationProvider vid="name" name="name" v-slot="{ errors }">
                                        <input v-model="name" :class="{
                                            'form-control': true,
                                            'is-invalid': errors.length > 0,
                                        }" id="edit-name" required>
                                        <div class="invalid-feedback">
                                            {{ errors[0] }}
                                        </div>
                                    </ValidationProvider>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label" for="edit-group">{{ $t('Group') }}</label>
                                <div class="col-sm-9">
                                    <ValidationProvider vid="group" name="group" v-slot="{ errors }">
                                        <select
                                            id="edit-group"
                                            :class="{
                                                'form-control': true,
                                                'is-invalid': errors.length > 0,
                                            }"
                                            v-model="group"
                                            required>
                                            <option value="picklist">{{ $t('Picklist') }}</option>
                                            <option value="packlist">{{ $t('Packlist') }}</option>
                                            <option value="reports">{{ $t('Reports') }}</option>
                                        </select>
                                        <div class="invalid-feedback">
                                            {{ errors[0] }}
                                        </div>
                                    </ValidationProvider>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label" for="url">{{ $t('Url') }}</label>
                                <div class="col-sm-9">
                                    <ValidationProvider vid="url" name="url" v-slot="{ errors }">
                                        <input v-model="url" :class="{
                                            'form-control': true,
                                            'is-invalid': errors.length > 0,
                                        }" id="create-url" required>
                                        <div class="invalid-feedback">
                                            {{ errors[0] }}
                                        </div>
                                    </ValidationProvider>
                                </div>
                            </div>
                        </form>
                    </ValidationObserver>
                </div>
                <div class="modal-footer">
                    <button type="button" @click="closeModal" class="btn btn-secondary">{{ $t('Cancel') }}</button>
                    <button type="button" @click="deleteNavigationMenu" class="btn btn-danger mr-auto">{{ $t('Delete') }}</button>
                    <button type="button" @click="submit" class="btn btn-primary">{{ $t('Save') }}</button>
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

    data() {
        return {
            name: '',
            url: '',
            group: '',
        }
    },

    props: {
        navigationMenu: Object,
    },

    watch: {
        navigationMenu: function(newVal) {
            this.name = newVal.name
            this.url = newVal.url
            this.group = newVal.group
        }
    },

    methods: {

        submit() {
            this.showLoading();
            this.apiPutNavigationMenu(this.navigationMenu.id, {
                    name: this.name,
                    url: this.url,
                    group: this.group,
                })
                .then(({ data }) => {
                    this.$snotify.success(this.$t('Navigation menu updated.'));
                    this.closeModal();
                    this.$emit('onUpdated', data.data);
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

        closeModal() {
            $(this.$el).modal('hide');
        }
        ,
        deleteNavigationMenu() {
            this.$snotify.confirm(this.$t('Once deleted, data cannot be restored'), this.$t('Are you sure?'), {
                position: 'centerCenter',
                buttons: [
                    {
                        text: this.$t('Yes'),
                        action: (toast) => {
                            this.showLoading();
                            this.apiDeleteNavigationMenu(this.navigationMenu.id)
                                .then(() => {
                                    this.$snotify.success(this.$t('Navigation menu deleted.'));
                                    this.closeModal();
                                    this.$emit('onDeleted', this.navigationMenu.id);
                                })
                                .finally(() => {
                                    this.hideLoading();
                                    this.$snotify.remove(toast.id);
                                });
                        }
                    },
                    {text: this.$t('Cancel')},
                ]
            });
        }
    },
}
</script>
