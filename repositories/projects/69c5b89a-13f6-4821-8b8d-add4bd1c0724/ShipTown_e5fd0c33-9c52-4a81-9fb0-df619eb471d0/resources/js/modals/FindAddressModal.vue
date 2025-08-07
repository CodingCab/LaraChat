<template>
    <b-modal body-class="ml-0 mr-0 pl-1 pr-1" :id="modalId" size="xl" scrollable no-fade hide-header>
        <search-and-option-bar>
            <barcode-input-field :input_id="'customer_search_input'" :placeholder="$t('Search')" :showKeyboardOnFocus="true"
                @barcodeScanned="findText" />
            <template v-slot:buttons>
                <button @click="showNewAddressModal" type="button" class="btn btn-primary ml-2">
                    <font-awesome-icon icon="plus" class="fa-lg"></font-awesome-icon>
                </button>
            </template>
        </search-and-option-bar>

        <template v-if="isLoading === false && addresses !== null && addresses.length === 0">
            <div class="text-secondary small text-center mt-3">
                {{ $t('No records found') }}<br>
                {{ $t('Click + to create one') }}<br>
            </div>
        </template>

        <table class="table-hover w-100 text-left small text-nowrap mx-2">
            <tr class="font-weight-bold">
                <td>{{ $t('Name') }}</td>
                <td>{{ $t('Company') }}</td>
                <td>{{ $t('Address 1') }}</td>
                <td>{{ $t('Address 2') }}</td>
                <td>{{ $t('City') }}</td>
                <td>{{ $t('Post Code') }}</td>
                <td></td>
            </tr>
            <tr v-for="address in addresses"
                :class="{ 'addresses__item--selected': selectedShippingAddress === address.id || selectedBillingAddress === address.id }"
                :key="address.id">
                <td>{{ address?.first_name ?? '-' }} {{ address?.last_name ?? '-' }}</td>
                <td>{{ address?.company ?? '-' }}</td>
                <td>{{ address?.address1 ?? '-' }}</td>
                <td>{{ address?.address2 ?? '-' }}</td>
                <td>{{ address?.city ?? '-' }}</td>
                <td>{{ address?.postcode ?? '-' }}</td>
                <td>
                    <button v-if="customer" class="btn btn-primary btn-sm"
                            :class="{ 'disabled': selectedShippingAddress !== address.id }"
                            @click="selectShippingAddress(address.id)">
                        <template v-if="selectedShippingAddress === address.id">{{ $t('SHIPPING') }}</template>
                        <template v-else>{{ $t('SHIPPING') }}</template>
                    </button>
                    <button v-if="customer" class="btn btn-primary btn-sm"
                        :class="{ 'disabled': selectedBillingAddress !== address.id }"
                        @click="selectBillingAddress(address.id)">
                        <template v-if="selectedBillingAddress === address.id">{{ $t('BILLING') }}</template>
                        <template v-else>{{ $t('BILLING') }}</template>
                    </button>
                    <button v-if="warehouse" class="addresses__itemButton"
                        :class="{ 'addresses__itemButton--clicked': selectedWarehouseAddress === address.id }"
                        @click="selectWarehouseAddress(address.id)">
                        <template v-if="selectedWarehouseAddress === address.id">{{ $t('SELECTED') }}</template>
                        <template v-else>{{ $t('SELECT') }}</template>
                    </button>
                </td>
            </tr>
        </table>

        <div class="row">
            <div class="col">
                <div ref="loadingContainerOverride" style="height: 32px"></div>
            </div>
        </div>

        <template #modal-footer>
            <b-button variant="secondary" class="float-right" @click="closeModal(false)">
                {{ $t('Cancel') }}
            </b-button>
            <b-button variant="primary" class="float-right" @click="closeModal(true)">
                {{ $t('OK') }}
            </b-button>
        </template>
    </b-modal>
</template>

<script>
import api from "../mixins/api.vue";
import url from "../mixins/url.vue";
import loadingOverlay from "../mixins/loading-overlay";
import Modals from "../plugins/Modals";

export default {
    components: {},

    mixins: [loadingOverlay, api, url],

    props: {
        transactionDetails: {
            type: Object,
            required: false,
        },
    },

    beforeMount() {
        Modals.EventBus.$on(`show::modal::${this.modalId}`, (data) => {
            if (typeof data.type !== 'undefined') {
                if (data.type === 'warehouse') {
                    this.warehouse = true;
                    this.selectedWarehouseAddress = data.warehouse?.address?.id ?? null;
                } else {
                    this.customer = true;
                }
            }

            this.$bvModal.show(this.modalId);
        })
    },

    mounted() {
        if (this.transactionDetails) {
            this.selectedBillingAddress = this.transactionDetails?.billing_address_id ?? null;
            this.selectedShippingAddress = this.transactionDetails?.shipping_address_id ?? null;
        }

        if (this.warehouse) {
            this.selectedWarehouseAddress = this.warehouse?.address?.id ?? null;
        }

        Modals.EventBus.$on('hide::modal::new-address-modal', (data) => {
            if (typeof data.addressSaved !== 'undefined' && data.addressSaved) {
                this.searchText = '';
                this.addresses.unshift(data.address);
                if (this.customer) {
                    if (this.selectedBillingAddress === null) {
                        this.selectedBillingAddress = data.address.id;
                    }
                    if (this.selectedShippingAddress === null) {
                        this.selectedShippingAddress = data.address.id;
                    }
                } else if (this.warehouse) {
                    this.selectedWarehouseAddress = data.address.id;
                }
            }
        });

        this.findText('');
    },

    data() {
        return {
            callback: null,
            modalId: 'find-address-modal',
            addresses: [],
            selectedBillingAddress: null,
            selectedShippingAddress: null,
            customer: false,
            warehouse: false,
            selectedWarehouseAddress: null,
            searchText: ''
        }
    },

    methods: {
        selectBillingAddress(addressId) {
            this.selectedBillingAddress = addressId;
        },

        selectShippingAddress(addressId) {
            this.selectedShippingAddress = addressId;
        },

        selectWarehouseAddress(addressId) {
            this.selectedWarehouseAddress = addressId;
        },

        findText(searchText) {
            this.searchText = searchText;
            this.findAddressContainingSearchText();
        },

        findAddressContainingSearchText() {
            this.showLoading();

            const params = {};
            params['filter[search]'] = this.searchText;

            this.apiGetOrderAddresses(params)
                .then(({ data }) => {
                    this.addresses = data.data;
                })
                .catch((error) => {
                    this.displayApiCallError(error);
                })
                .finally(() => {
                    this.hideLoading();
                    this.setFocusElementById('#customer_search_input');
                });
            return this;
        },

        closeModal(saveChanges) {
            this.$bvModal.hide(this.modalId);

            Modals.EventBus.$emit(`hide::modal::${this.modalId}`, {
                billingAddress: this.selectedBillingAddress,
                shippingAddress: this.selectedShippingAddress,
                warehouseAddress: this.selectedWarehouseAddress,
                saveChanges: saveChanges
            });
        },

        showNewAddressModal() {
            this.$modal.showAddNewAddressModal();
        }
    }
};

</script>

<style lang="scss" scoped>
.addresses {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;

    &__item {
        flex: 0 0 calc(33.33333% - 20px + (20px / 3));
        max-width: calc(33.33333% - 20px + (20px / 3));
        padding: 10px;
        border: 1px solid #ced4da;
        border-radius: 4px;

        &--selected {
            border-color: #227dc7;
        }
    }

    &__itemButtons {
        gap: 10px;
    }

    &__itemButton {
        padding: 5px 10px;
        border: 1px solid #ced4da;
        border-radius: 4px;
        background-color: #f8f9fa;
        cursor: pointer;
        transition: background-color 0.3s, color 0.3s;

        &--clicked {
            background-color: #227dc7;
            color: white;
        }
    }

    @media all and (max-width: 576px) {
        &__item {
            flex: 0 0 calc(50% - 20px + (20px / 2));
            max-width: calc(50% - 20px + (20px / 2));
        }
    }
}
</style>
