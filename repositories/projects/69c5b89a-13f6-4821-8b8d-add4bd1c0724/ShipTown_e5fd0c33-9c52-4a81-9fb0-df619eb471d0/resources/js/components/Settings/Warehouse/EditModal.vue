<template>
    <div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div ref="loadingContainer2" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $t('Edit Warehouse') }}</h5>
                </div>
                <div class="modal-body">
                    <ValidationObserver ref="form">
                        <form class="form" @submit.prevent="submit" ref="loadingContainer">
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label" for="code">{{ $t('Code') }}</label>
                                <div class="col-sm-9">
                                    <ValidationProvider vid="code" name="code" v-slot="{ errors }">
                                        <input v-model="code" :class="{
                                            'form-control': true,
                                            'is-invalid': errors.length > 0,
                                        }" id="edit-code" required>
                                        <div class="invalid-feedback">{{ errors[0] }}</div>
                                    </ValidationProvider>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label" for="name">{{ $t('Name') }}</label>
                                <div class="col-sm-9">
                                    <ValidationProvider vid="name" name="name" v-slot="{ errors }">
                                        <input v-model="name" :class="{
                                            'form-control': true,
                                            'is-invalid': errors.length > 0,
                                        }" id="edit-name" required>
                                        <div class="invalid-feedback">{{ errors[0] }}</div>
                                    </ValidationProvider>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label" for="tagsString">{{ $t('Tags') }}</label>
                                <div class="col-sm-9">
                                    <ValidationProvider vid="tagsString" name="tagsString" v-slot="{ errors }">
                                        <input v-model="tagsString" :class="{
                                            'form-control': true,
                                            'is-invalid': errors.length > 0,
                                        }" id="edit-tags" required>
                                        <div class="invalid-feedback">{{ errors[0] }}</div>
                                    </ValidationProvider>
                                </div>
                            </div>
                        </form>
                    </ValidationObserver>
                    <div v-if="warehouse['address']" class="row">
                        <div class="col-3">
                            <div class="col-form-label">{{ $t('Address') }}</div>
                        </div>
                        <div class="col-9">
                            <div class="small">
                                {{ warehouse['address']['first_name'] ?? '' }}
                                {{ warehouse['address']['last_name'] ?? '' }}
                                <br>
                                {{ warehouse['address']['company'] ?? '' }}
                                <br>
                                {{ warehouse['address']['address1'] ?? '' }}
                                <br>
                                {{ warehouse['address']['address2'] ?? '' }}
                                <br>
                                {{ warehouse['address']['city'] ?? '' }}
                                {{ warehouse['address']['postcode'] ?? '' }}
                                <br>
                                {{ warehouse['address']['country_name'] ?? '' }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="justify-content:space-between">
                    <button type="button" @click.prevent="confirmDelete(warehouse)"
                        class="btn btn-outline-danger float-left">{{ $t('Delete') }}</button>
                    <div>
                        <button type="button" @click="closeModal" class="btn btn-secondary">{{ $t('Cancel') }}</button>
                        <button type="button" @click.prevent="selectAddress" class="btn btn-outline-primary">
                            {{ $t('Select Address') }}
                        </button>
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

    data() {
        return {
            name: '',
            code: '',
            tagsString: '',
            tags: [],
        }
    },

    props: {
        warehouse: Object,
    },

    watch: {
        warehouse: function (newVal) {
            this.name = newVal.name;
            this.code = newVal.code;
            this.tags = newVal.tags;
            this.tagsString = newVal.tags
                .map(function ($tag) {
                    return $tag['name'];
                })
                .join(',');
        }
    },

    methods: {
        submit() {
            this.showLoading();
            this.apiPutWarehouses(this.warehouse.id, {
                name: this.name,
                code: this.code,
                tags: this.tagsString.split(','),
            })
                .then(({ data }) => {
                    this.closeModal();
                    this.$emit('onUpdated', data.data);
                })
                .catch((error) => {
                    console.log(error, 'error');
                    if (error.response) {
                        if (error.response.status === 422) {
                            this.$refs.form.setErrors(error.response.data.errors);
                        } else {
                            this.displayApiCallError(error);
                        }
                    }
                })
                .finally(this.hideLoading);
        },

        closeModal() {
            $(this.$el).modal('hide');
        },

        confirmDelete(selectedWarehouse) {
            this.$snotify.confirm(this.$t('Once deleted, data cannot be restored'), this.$t('Are you sure?'), {
                position: 'centerCenter',
                buttons: [
                    {
                        text: this.$t('Yes'),
                        action: (toast) => {
                            this.apiDeleteWarehouses(selectedWarehouse.id)
                                .then(() => {
                                    this.$snotify.success(this.$t('Warehouse has been deleted.'));
                                })
                                .catch(() => {
                                    this.$snotify.error(this.$t('Error occurred while deleting.'));
                                });
                            this.$snotify.remove(toast.id);
                        }
                    },
                    { text: this.$t('Cancel') },
                ]
            });
        },

        selectAddress() {
            $('#editForm').modal('hide');
            this.$modal.showFindAddressModal({
                type: 'warehouse',
                warehouse: this.warehouse,
            });
        },
    },
}
</script>
