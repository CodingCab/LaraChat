<template>
    <div ref="form">
        <form class="form" @submit.prevent="submit" ref="loadingContainer">
            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="account_number">{{ $t('Account Number') }}</label>
                <div class="col-sm-9">
                    <input v-model="connection['account_number']" class="form-control" id='account_number' :placeholder="$t('Account Number')" required>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="username">{{ $t('Username') }}</label>
                <div class="col-sm-9">
                    <input type="text" v-model="connection['username']" class="form-control" id="username" :placeholder="$t('Username')" required>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="password">{{ $t('Password') }}</label>
                <div class="col-sm-9">
                    <input type="password" v-model="connection['password']" class="form-control" id="password" :placeholder="$t('Password')" required>
                </div>
            </div>

            <div class="row h5 mt-5">{{ $t('Collection Address') }}</div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="contact_name">{{ $t('Contact Name') }}</label>
                <div class="col-sm-9">
                    <input v-model="collectionAddress['full_name']" class="form-control" id="contact_name" :placeholder="$t('Contact Name')" required>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3  col-form-label" for="company">{{ $t('Business Name') }}</label>
                <div class="col-sm-9 ">
                    <input v-model="collectionAddress['company']" class="form-control" id="company" :placeholder="$t('Company Name')"/>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3  col-form-label" for="email">{{ $t('Contact Email') }}</label>
                <div class="col-sm-9 ">
                    <input type="email" v-model="collectionAddress['email']" class="form-control" id="email" :placeholder="$t('Contact Email')"/>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3  col-form-label" for="phone">{{ $t('Telephone') }}</label>
                <div class="col-sm-9 ">
                    <input v-model="collectionAddress['phone']" class="form-control" id="phone" :placeholder="$t('Telephone')" required>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3  col-form-label" for="address1">{{ $t('Address Line 1') }}</label>
                <div class="col-sm-9 ">
                    <input v-model="collectionAddress['address1']" class="form-control" id="address1" :placeholder="$t('Address Line 1')">
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3  col-form-label" for="address2">{{ $t('Address Line 2') }}</label>
                <div class="col-sm-9 ">
                        <input v-model="collectionAddress['address2']" class="form-control" id="address2" :placeholder="$t('Address Line 2')"/>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3  col-form-label" for="address3">{{ $t('Address Line 3') }}</label>
                <div class="col-sm-9 ">
                    <input v-model="collectionAddress['address3']" class="form-control" id="address3" :placeholder="$t('Address Line 3')" required>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3  col-form-label" for="city">{{ $t('City') }}</label>
                <div class="col-sm-9 ">
                    <input v-model="collectionAddress['city']" class="form-control" id="city" :placeholder="$t('City')" required>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3  col-form-label" for="postcode">{{ $t('Postcode') }}</label>
                <div class="col-sm-9 ">
                    <input v-model="collectionAddress['postcode']" class="form-control" id="postcode" :placeholder="$t('Postcode')" required>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3  col-form-label" for="state_name">{{ $t('State Name') }}</label>
                <div class="col-sm-9 ">
                    <input v-model="collectionAddress['state_name']" class="form-control" id="state_name" :placeholder="$t('State name')" required>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="country_code">{{ $t('Country Code') }}</label>
                <div class="col-sm-9">
                    <select v-model="collectionAddress['country_code']" class="form-control" id="country_code">
                        <option value="" selected disabled>{{ $t('Select an option') }}</option>
                        <option value="IE">IE</option>
                        <option value="IRL">IRL</option>
                        <option value="UK">UK</option>
                        <option value="GB">GB</option>
                    </select>
                </div>
            </div>
        </form>
    </div>
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

    data: () => ({
        connection: {},
        collectionAddress: {},
    }),

    methods: {
        submit() {
            this.showLoading();

            this.connection['collection_address'] = this.collectionAddress;

            this.apiPostDpdUkConnection(this.connection)
                .then(() => {
                    this.$emit("saved", this.connection);
                })
                .catch((error) => {
                    this.displayApiCallError(error);
                })
                .finally(() =>{
                        this.hideLoading();
                    }
                );
        },
    },
};
</script>
