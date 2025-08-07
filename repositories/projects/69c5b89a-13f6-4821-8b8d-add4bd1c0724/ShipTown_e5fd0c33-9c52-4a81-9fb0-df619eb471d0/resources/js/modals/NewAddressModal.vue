<template>
    <b-modal body-class="ml-0 mr-0 pl-1 pr-1" :id="modalId" :title="$t('Select or create new shipping/billing address')" size="xl" scrollable
        no-fade>
        <template #modal-header>
            <span>{{ $t('New Address') }}</span>
        </template>

        <div class="container">
            <ValidationObserver v-slot="{ handleSubmit }" ref="form">
                <form @submit.prevent="handleSubmit(createNewAddress)" ref="loadingContainer" class="addressForm">
                    <div class="d-flex align-items-center addressForm__wrapper">
                        <div class="form-group addressForm__item">
                            <label class="form-label" for="newAddressFirstName">{{ $t('First Name') }}</label>
                            <ValidationProvider vid="newAddressFirstName" name="newAddressFirstName" v-slot="{ errors }">
                                <input v-model="newAddress.first_name" type="text" :disabled="!isCreatingAddress"
                                    :class="{ 'form-control': true, 'is-invalid': errors.length > 0 }" id="newAddressFirstName"
                                    placeholder="John" required>
                                <div class="invalid-feedback">
                                    {{ errors[0] }}
                                </div>
                            </ValidationProvider>
                        </div>
                        <div class="form-group addressForm__item">
                            <label class="form-label" for="newAddressLastName">{{ $t('Last Name') }}</label>
                            <ValidationProvider vid="newAddressLastName" name="newAddressLastName" v-slot="{ errors }">
                                <input v-model="newAddress.last_name" type="text" :disabled="!isCreatingAddress"
                                    :class="{ 'form-control': true, 'is-invalid': errors.length > 0 }" id="newAddressLastName"
                                    placeholder="Doe" required>
                                <div class="invalid-feedback">
                                    {{ errors[0] }}
                                </div>
                            </ValidationProvider>
                        </div>
                        <div class="form-group addressForm__item">
                            <label class="form-label" for="newAddressGender">{{ $t('Gender') }}</label>
                            <ValidationProvider vid="newAddressGender" name="newAddressGender" v-slot="{ errors }">
                                <select v-model="newAddress.gender" name="newAddressGender" id="newAddressGender"
                                    :disabled="!isCreatingAddress" :class="{ 'form-control': true, 'is-invalid': errors.length > 0 }">
                                    <option value="" disabled selected>{{ $t('Gender') }}</option>
                                    <option v-for="(gender, index) in genders" :value="gender" :key="`type${index}`">
                                        {{ gender }}
                                    </option>
                                </select>
                                <div class="invalid-feedback">
                                    {{ errors[0] }}
                                </div>
                            </ValidationProvider>
                        </div>
                        <div class="form-group addressForm__item">
                            <label class="form-label" for="newAddressFirstLine">{{ $t('Address Line 1') }}</label>
                            <ValidationProvider vid="newAddressFirstLine" name="newAddressFirstLine" v-slot="{ errors }">
                                <input v-model="newAddress.address1" type="text" :disabled="!isCreatingAddress"
                                    :class="{ 'form-control': true, 'is-invalid': errors.length > 0 }" id="newAddressFirstLine"
                                    placeholder="215 E Tasman Dr">
                                <div class="invalid-feedback">
                                    {{ errors[0] }}
                                </div>
                            </ValidationProvider>
                        </div>
                        <div class="form-group addressForm__item">
                            <label class="form-label" for="newAddressSecondLine">{{ $t('Address Line 2') }}</label>
                            <ValidationProvider vid="newAddressSecondLine" name="newAddressSecondLine" v-slot="{ errors }">
                                <input v-model="newAddress.address2" type="text" :disabled="!isCreatingAddress"
                                    :class="{ 'form-control': true, 'is-invalid': errors.length > 0 }" id="newAddressSecondLine"
                                    placeholder="Po Box 65502">
                                <div class="invalid-feedback">
                                    {{ errors[0] }}
                                </div>
                            </ValidationProvider>
                        </div>
                        <div class="form-group addressForm__item">
                            <label class="form-label" for="newAddressPostCode">{{ $t('Post Code') }}</label>
                            <ValidationProvider vid="newAddressPostCode" name="newAddressPostCode" v-slot="{ errors }">
                                <input v-model="newAddress.postcode" type="text" :disabled="!isCreatingAddress"
                                    :class="{ 'form-control': true, 'is-invalid': errors.length > 0 }" id="newAddressPostCode"
                                    placeholder="95132">
                                <div class="invalid-feedback">
                                    {{ errors[0] }}
                                </div>
                            </ValidationProvider>
                        </div>
                        <div class="form-group addressForm__item">
                            <label class="form-label" for="newAddressCity">{{ $t('City') }}</label>
                            <ValidationProvider vid="newAddressCity" name="newAddressCity" v-slot="{ errors }">
                                <input v-model="newAddress.city" type="text" :disabled="!isCreatingAddress"
                                    :class="{ 'form-control': true, 'is-invalid': errors.length > 0 }" id="newAddressCity"
                                    placeholder="San Jose">
                                <div class="invalid-feedback">
                                    {{ errors[0] }}
                                </div>
                            </ValidationProvider>
                        </div>
                        <div class="form-group addressForm__item">
                            <label class="form-label" for="newAddressCountryCode">{{ $t('Country Code') }}</label>
                            <ValidationProvider vid="newAddressCountryCode" name="newAddressCountryCode" v-slot="{ errors }">
                                <select v-model="newAddress.country_code" name="newAddressCountryCode" id="newAddressCountryCode"
                                    :disabled="!isCreatingAddress" :class="{ 'form-control': true, 'is-invalid': errors.length > 0 }">
                                    <option value="" selected disabled>{{ $t('Select an option') }}</option>
                                    <option value="IE">IE</option>
                                    <option value="IRL">IRL</option>
                                    <option value="UK">UK</option>
                                    <option value="GB">GB</option>
                                    <option value="PL">PL</option>
                                </select>
                                <div class="invalid-feedback">
                                    {{ errors[0] }}
                                </div>
                            </ValidationProvider>
                        </div>
                        <div class="form-group addressForm__item">
                            <label class="form-label" for="newAddressCompanyName">{{ $t('Company Name') }}</label>
                            <ValidationProvider vid="newAddressCompanyName" name="newAddressCompanyName" v-slot="{ errors }">
                                <input v-model="newAddress.company" type="text" :disabled="!isCreatingAddress"
                                    :class="{ 'form-control': true, 'is-invalid': errors.length > 0 }" id="newAddressCompanyName"
                                    placeholder="Confidential LTD.">
                                <div class="invalid-feedback">
                                    {{ errors[0] }}
                                </div>
                            </ValidationProvider>
                        </div>
                        <div class="form-group addressForm__item">
                            <label class="form-label" for="newAddressEmail">{{ $t('Email') }}</label>
                            <ValidationProvider vid="newAddressEmail" name="newAddressEmail" v-slot="{ errors }">
                                <input v-model="newAddress.email" type="email" :disabled="!isCreatingAddress"
                                    :class="{ 'form-control': true, 'is-invalid': errors.length > 0 }" id="newAddressEmail"
                                    placeholder="john@example.com">
                                <div class="invalid-feedback">
                                    {{ errors[0] }}
                                </div>
                            </ValidationProvider>
                        </div>
                        <div class="form-group addressForm__item">
                            <label class="form-label" for="newAddressPhoneNumber">{{ $t('Phone') }}</label>
                            <ValidationProvider vid="newAddressPhoneNumber" name="newAddressPhoneNumber" v-slot="{ errors }">
                                <input v-model="newAddress.phone" type="text" :disabled="!isCreatingAddress"
                                    :class="{ 'form-control': true, 'is-invalid': errors.length > 0 }" id="newAddressPhoneNumber"
                                    placeholder="+353 1 344 1111">
                                <div class="invalid-feedback">
                                    {{ errors[0] }}
                                </div>
                            </ValidationProvider>
                        </div>
                        <div class="form-group addressForm__item">
                            <label class="form-label" for="newAddressTaxId">{{ $t('Tax ID') }}</label>
                            <ValidationProvider vid="newAddressTaxId" name="newAddressTaxId" v-slot="{ errors }">
                                <input v-model="newAddress.tax_id" type="text" :disabled="!isCreatingAddress"
                                    :class="{ 'form-control': true, 'is-invalid': errors.length > 0 }" id="newAddressTaxId"
                                    placeholder="1234567890">
                                <div class="invalid-feedback">
                                    {{ errors[0] }}
                                </div>
                            </ValidationProvider>
                        </div>
                        <div class="form-group addressForm__item">
                            <label class="form-label" for="newAddressDocumentType">{{ $t('Document Type') }}</label>
                            <ValidationProvider vid="newAddressDocumentType" name="newAddressDocumentType" v-slot="{ errors }">
                                <select v-model="newAddress.document_type" name="newAddressDocumentType" id="newAddressDocumentType"
                                    :disabled="!isCreatingAddress" :class="{ 'form-control': true, 'is-invalid': errors.length > 0 }">
                                    <option value="" disabled selected>{{ $t('Document Type') }}</option>
                                    <option v-for="(documentType, index) in documentTypes" :value="documentType" :key="`type${index}`">
                                        {{ documentType }}
                                    </option>
                                </select>
                                <div class="invalid-feedback">
                                    {{ errors[0] }}
                                </div>
                            </ValidationProvider>
                        </div>
                        <div class="form-group addressForm__item">
                            <label class="form-label" for="newAddressDocumentNumber">{{ $t('Document Number') }}</label>
                            <ValidationProvider vid="newAddressDocumentNumber" name="newAddressDocumentNumber" v-slot="{ errors }">
                                <input v-model="newAddress.document_number" type="text" :disabled="!isCreatingAddress"
                                    :class="{ 'form-control': true, 'is-invalid': errors.length > 0 }" id="newAddressDocumentNumber"
                                    placeholder="1234567890">
                                <div class="invalid-feedback">
                                    {{ errors[0] }}
                                </div>
                            </ValidationProvider>
                        </div>
                        <div class="form-group addressForm__item">
                            <label class="form-label" for="newAddressDiscountCode">{{ $t('Discount Code') }}</label>
                            <ValidationProvider vid="newAddressDiscountCode" name="newAddressDiscountCode" v-slot="{ errors }">
                                <input v-model="newAddress.discount_code" type="text" :disabled="!isCreatingAddress"
                                    :class="{ 'form-control': true, 'is-invalid': errors.length > 0 }" id="newAddressDiscountCode"
                                    placeholder="DISCOUNT10">
                                <div class="invalid-feedback">
                                    {{ errors[0] }}
                                </div>
                            </ValidationProvider>
                        </div>
                        <div class="form-group form-check addressForm__item">
                            <input v-model="newAddress.tax_exempt" type="checkbox" class="form-check-input" id="newAddressTaxExempt" />
                            <label class="form-check-label" for="newAddressTaxExempt">Tax Exempt</label>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end">
                        <b-button variant="secondary" class="mr-2" @click="closeModal">{{ $t('Cancel') }}</b-button>
                        <b-button variant="primary" type="submit">{{ $t('Create') }}</b-button>
                    </div>
                </form>
            </ValidationObserver>
        </div>
        <template #modal-footer>
            <div></div>
        </template>
    </b-modal>
</template>

<script>

import ProductCard from "../components/Products/ProductCard.vue";
import api from "../mixins/api.vue";
import Modals from "../plugins/Modals";
import { ValidationObserver, ValidationProvider } from "vee-validate";
import loadingOverlay from "../mixins/loading-overlay";

export default {
    components: { ProductCard, ValidationObserver, ValidationProvider },

    mixins: [api, loadingOverlay],

    beforeMount() {
        Modals.EventBus.$on('show::modal::' + this.modalId, (data) => {
            this.address = data['address'];

            this.newAddress = {
                first_name: '',
                last_name: '',
                gender: '',
                address1: '',
                address2: '',
                postcode: '',
                city: '',
                country_code: '',
                company: '',
                email: '',
                phone: '',
                tax_id: '',
                document_type: '',
                document_number: '',
                discount_code: '',
            };

            if (this.address) {
                this.newAddress.first_name = this.address.first_name;
                this.newAddress.last_name = this.address.last_name;
                this.newAddress.gender = this.address.gender;
                this.newAddress.address1 = this.address.address1;
                this.newAddress.address2 = this.address.address2;
                this.newAddress.postcode = this.address.postcode;
                this.newAddress.city = this.address.city;
                this.newAddress.country_code = this.address.country_code;
                this.newAddress.company = this.address.company;
                this.newAddress.email = this.address.email;
                this.newAddress.phone = this.address.phone;
                this.newAddress.tax_id = this.address.tax_id;
                this.newAddress.document_type = this.address.document_type;
                this.newAddress.document_number = this.address.document_number;
                this.newAddress.discount_code = this.address.discount_code;
            }

            this.$bvModal.show(this.modalId);
        })
    },

    data() {
        return {
            newAddress: {
                first_name: '',
                last_name: '',
                gender: '',
                address1: '',
                address2: '',
                postcode: '',
                city: '',
                country_code: '',
                company: '',
                email: '',
                phone: '',
                tax_exempt: false,
                tax_id: '',
                document_type: '',
                document_number: '',
                discount_code: '',
            },
            genders: ['Ms.', 'Mr.', 'Mrs.', 'Dr.', 'Prof.'],
            modalId: 'new-address-modal',
            address: undefined,
            documentTypes: ['Passport', 'Driving License', 'ID Card', 'Other']
        }
    },

    computed: {
        isCreatingAddress() {
            return this.address === null || (this.address === undefined);
        }
    },

    methods: {
        createNewAddress() {
            this.showLoading();
            this.apiPostOrderAddress(this.newAddress)
                .then(response => {
                    this.$bvModal.hide(this.modalId);
                    Modals.EventBus.$emit(`hide::modal::${this.modalId}`, {
                        address: response.data.data,
                        addressSaved: true
                    });
                    this.$snotify.success(this.$t('Address created successfully'));
                })
                .catch(error => {
                    if (typeof error.response !== 'undefined' && typeof error.response.data !== 'undefined' && typeof error.response.data.errors !== 'undefined') {
                        const { discount_code } = error.response.data.errors;
                        if (discount_code) {
                            this.$refs.form.setErrors({ newAddressDiscountCode: discount_code });
                        }
                    } else {
                        this.displayApiCallError(error);
                    }
                })
                .finally(() => {
                    this.hideLoading();
                })
        },

        closeModal() {
            this.$bvModal.hide(this.modalId);
            Modals.EventBus.$emit(`hide::modal::${this.modalId}`, {
                addressSaved: false
            });
        }
    },

    watch: {
        'newAddress.country_code': function (newVal) {
            this.newAddress.country_name = newVal === 'IE' || newVal === 'IRL' ? 'Ireland' : 'United Kingdom';
        }
    }
};

</script>

<style lang="scss" scoped>
.addressForm {
    &__wrapper {
        flex-wrap: wrap;
        gap: 10px;
    }

    &__item {
        flex: 0 0 calc(50% - 10px + (10px / 2));
        max-width: calc(50% - 10px + (10px / 2));
    }
}

@media all and (max-width: 768px) {
    .addressForm {
        &__item {
            flex: 0 0 100%;
            max-width: 100%;
        }
    }
}
</style>
