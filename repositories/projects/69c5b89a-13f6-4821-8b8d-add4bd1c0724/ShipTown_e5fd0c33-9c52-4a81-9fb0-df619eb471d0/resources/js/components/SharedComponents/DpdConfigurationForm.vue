<template>
    <ValidationObserver ref="form">
        <form class="form" @submit.prevent="submit" ref="loadingContainer">
            <div class="form-group row" v-if="is_new">
                <label class="col-sm-3 col-form-label" for="api_username">{{ $t('API Username') }}</label>
                <div class="col-sm-9">
                    <ValidationProvider name="user" v-slot="{ errors }">
                        <input
                            v-model="api_username"
                            :class="{
                                'form-control': true,
                                'is-invalid': errors.length > 0,
                            }"
                            id="api_username"
                            :placeholder="$t('API Username')"
                            :required="is_new"
                        />
                        <div class="invalid-feedback">
                            {{ errors[0] }}
                        </div>
                    </ValidationProvider>
                </div>
            </div>

            <div class="form-group row" v-if="is_new">
                <label class="col-sm-3 col-form-label" for="api_token">{{ $t('API Token') }}</label>
                <div class="col-sm-9">
                    <ValidationProvider name="token" v-slot="{ errors }">
                        <input
                            v-model="token"
                            :class="{
                                'form-control': true,
                                'is-invalid': errors.length > 0,
                            }"
                            id="api_token"
                            placeholder="API Token"
                            :required="is_new"
                        />
                        <div class="invalid-feedback">
                            {{ errors[0] }}
                        </div>
                    </ValidationProvider>
                </div>
            </div>
            <div class="form-group row" v-if="is_new">
                <label class="col-sm-3 col-form-label" for="api_password">{{ $t('API Password') }}</label>
                <div class="col-sm-9">
                    <ValidationProvider name="password" v-slot="{ errors }">
                        <input
                            v-model="password"
                            :class="{
                                'form-control': true,
                                'is-invalid': errors.length > 0,
                            }"
                            id="api_password"
                            :placeholder="$t('API Password')"
                            :required="is_new"
                        />
                        <div class="invalid-feedback">
                            {{ errors[0] }}
                        </div>
                    </ValidationProvider>
                </div>
            </div>
            <div class="form-group row">
                <div class="custom-control custom-switch">
                    <input
                        type="checkbox"
                        class="custom-control-input"
                        id="live"
                        v-model="live"
                        required
                    />
                    <label class="custom-control-label" for="live">{{ $t('Live Mode') }}</label>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="this.contact">{{ $t('Contact Name') }}</label>
                <div class="col-sm-9">
                    <ValidationProvider name="contact_name" v-slot="{ errors }">
                        <input
                            v-model="contact"
                            :class="{
                                'form-control': true,
                                'is-invalid': errors.length > 0,
                            }"
                            id="url"
                            :placeholder="$t('Contact Name')"
                            required
                        />
                        <div class="invalid-feedback">
                            {{ errors[0] }}
                        </div>
                    </ValidationProvider>
                </div>
            </div>

            <div class="form-group row">
                <label
                    class="col-sm-3  col-form-label"
                    for="business_name"
                    >{{ $t('Business Name') }}</label
                >
                <div class="col-sm-9 ">
                    <ValidationProvider
                        name="business_name"
                        v-slot="{ errors }"
                    >
                        <input
                            v-model="business_name"
                            :class="{
                                'form-control': true,
                                'is-invalid': errors.length > 0,
                            }"
                            id="url"
                            :placeholder="$t('Business Name')"
                        />
                        <div class="invalid-feedback">
                            {{ errors[0] }}
                        </div>
                    </ValidationProvider>
                </div>
            </div>

            <div class="form-group row">
                <label
                    class="col-sm-3  col-form-label"
                    for="contact_email"
                    >{{ $t('Contact Email') }}</label
                >
                <div class="col-sm-9 ">
                    <ValidationProvider
                        name="contact_email"
                        v-slot="{ errors }"
                    >
                        <input
                            type="email"
                            v-model="contact_email"
                            :class="{
                                'form-control': true,
                                'is-invalid': errors.length > 0,
                            }"
                            id="url"
                            :placeholder="$t('Contact Email')"
                        />
                        <div class="invalid-feedback">
                            {{ errors[0] }}
                        </div>
                    </ValidationProvider>
                </div>
            </div>

            <div class="form-group row">
                <label
                    class="col-sm-3  col-form-label"
                    for="contact_telephone"
                    >{{ $t('Contact Telephone') }}</label
                >
                <div class="col-sm-9 ">
                    <ValidationProvider
                        name="contact_telephone"
                        v-slot="{ errors }"
                    >
                        <input
                            v-model="contact_telephone"
                            :class="{
                                'form-control': true,
                                'is-invalid': errors.length > 0,
                            }"
                            id="url"
                            :placeholder="$t('Contact Telephone')"
                            required
                        />
                        <div class="invalid-feedback">
                            {{ errors[0] }}
                        </div>
                    </ValidationProvider>
                </div>
            </div>

            <div class="form-group row">
                <label
                    class="col-sm-3  col-form-label"
                    for="address_line_1"
                    >{{ $t('Address Line 1') }}</label
                >
                <div class="col-sm-9 ">
                    <ValidationProvider
                        name="address_line_1"
                        v-slot="{ errors }"
                    >
                        <input
                            v-model="address_line_1"
                            :class="{
                                'form-control': true,
                                'is-invalid': errors.length > 0,
                            }"
                            id="url"
                            :placeholder="$t('Address Line 1')"
                        />
                        <div class="invalid-feedback">
                            {{ errors[0] }}
                        </div>
                    </ValidationProvider>
                </div>
            </div>

            <div class="form-group row">
                <label
                    class="col-sm-3  col-form-label"
                    for="address_line_2"
                    >{{ $t('Address Line 2') }}</label
                >
                <div class="col-sm-9 ">
                    <ValidationProvider
                        name="address_line_2"
                        v-slot="{ errors }"
                    >
                        <input
                            v-model="address_line_2"
                            :class="{
                                'form-control': true,
                                'is-invalid': errors.length > 0,
                            }"
                            id="url"
                            :placeholder="$t('Address Line 2')"
                        />
                        <div class="invalid-feedback">
                            {{ errors[0] }}
                        </div>
                    </ValidationProvider>
                </div>
            </div>

            <div class="form-group row">
                <label
                    class="col-sm-3  col-form-label"
                    for="address_line_3"
                    >{{ $t('Address Line 3') }}</label
                >
                <div class="col-sm-9 ">
                    <ValidationProvider
                        name="address_line_3"
                        v-slot="{ errors }"
                    >
                        <input
                            v-model="address_line_3"
                            :class="{
                                'form-control': true,
                                'is-invalid': errors.length > 0,
                            }"
                            id="url"
                            :placeholder="$t('Address Line 3')"
                            required
                        />
                        <div class="invalid-feedback">
                            {{ errors[0] }}
                        </div>
                    </ValidationProvider>
                </div>
            </div>

            <div class="form-group row">
                <label
                    class="col-sm-3  col-form-label"
                    for="address_line_4"
                    >{{ $t('Address Line 4') }}</label
                >
                <div class="col-sm-9 ">
                    <ValidationProvider
                        name="address_line_4"
                        v-slot="{ errors }"
                    >
                        <input
                            v-model="address_line_4"
                            :class="{
                                'form-control': true,
                                'is-invalid': errors.length > 0,
                            }"
                            id="url"
                            :placeholder="$t('Address Line 4')"
                            required
                        />
                        <div class="invalid-feedback">
                            {{ errors[0] }}
                        </div>
                    </ValidationProvider>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="country_code"
                    >{{ $t('Country Code') }}</label>
                <div class="col-sm-9">
                    <ValidationProvider name="country_code" v-slot="{ errors }">
                        <select
                            v-model="country_code"
                            :class="{
                                'form-control': true,
                                'is-invalid': errors.length > 0,
                            }"
                            id="type"
                            :placeholder="$t('Select')"
                            required
                        >
                            <option value="" selected disabled>
                                {{ $t('Select an option') }}
                            </option>
                            <option value="IE">IE</option>
                            <option value="IRL">IRL</option>
                            <option value="UK">UK</option>
                            <option value="GB">GB</option>
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
import { ValidationObserver, ValidationProvider } from "vee-validate";

import Loading from "../../mixins/loading-overlay";
import api from "../../mixins/api";

export default {
    components: {
        ValidationObserver,
        ValidationProvider,
    },

    mixins: [api, Loading],

    mounted() {
        if (
            this.configuration !== null &&
            this.configuration instanceof Object
        ) {
            this.populateConfiguration();
        }
    },

    computed: {
        is_new: function () {
            return this.configuration === null;
        },
    },

    data: () => ({
        live: false,
        contact: null,
        contact_telephone: null,
        contact_email: null,
        business_name: null,
        address_line_1: null,
        address_line_2: null,
        address_line_3: null,
        address_line_4: null,
        country_code: null,
        token: null,
        api_username: null,
        password: null,
    }),

    props: {
        configuration: {
            default: null,
            type: Object,
        },
    },

    methods: {
        populateConfiguration() {
            this.api_username = this.configuration.api_username;
            this.live = this.configuration.api_live;
            this.contact = this.configuration.collection_contact;
            this.contact_telephone = this.configuration.collection_telephone;
            this.contact_email = this.configuration.collection_email;
            this.business_name = this.configuration.collection_business_name;
            this.address_line_1 = this.configuration.collection_address_line_1;
            this.address_line_2 = this.configuration.collection_address_line_2;
            this.address_line_3 = this.configuration.collection_address_line_3;
            this.address_line_4 = this.configuration.collection_address_line_4;
            this.country_code = this.configuration.collection_country_code;
        },

        submit() {
            this.showLoading();

            let data = {
                user: this.api_username,
                live: this.live,
                contact: this.contact,
                contact_telephone: this.contact_telephone,
                contact_email: this.contact_email,
                business_name: this.business_name,
                address_line_1: this.address_line_1,
                address_line_2: this.address_line_2,
                address_line_3: this.address_line_3,
                address_line_4: this.address_line_4,
                country_code: this.country_code,
            };

            if (!this.is_new) {
                if (!!this.password) {
                    data.password = this.password;
                }
                if (!!this.token) {
                    data.token = this.token;
                }
            } else {
                data.password = this.password;
                data.token = this.token;
            }

            this.apiPostDpdConfiguration(data)
                .then(() => {
                    this.$emit("saved", {
                            api_live: this.live,
                            user: this.api_username,
                            collection_contact: this.contact,
                            collection_telephone: this.contact_telephone,
                            collection_email: this.contact_email,
                            collection_business_name: this.business_name,
                            collection_address_line_1: this.address_line_1,
                            collection_address_line_2: this.address_line_2,
                            collection_address_line_3: this.address_line_3,
                            collection_address_line_4: this.address_line_4,
                            collection_country_code: this.country_code,
                        });
                })
                .catch((error) => {
                    if (error.response) {
                        if (error.response.status === 422) {
                            this.$refs.form.setErrors(
                                error.response.data.errors
                            );
                        }
                    }
                })
                .finally(() =>{
                        this.hideLoading();
                    }
                );
        },
    },
};
</script>
