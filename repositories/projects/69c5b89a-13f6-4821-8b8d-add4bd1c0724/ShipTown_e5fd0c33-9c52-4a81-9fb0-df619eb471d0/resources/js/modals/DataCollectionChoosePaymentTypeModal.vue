<template>
    <b-modal body-class="ml-0 mr-0 p-0" :id="modalId" size="md" scrollable no-fade hide-header hide-footer>
        <div class="card card-default mb-0">
            <div class="card-header">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span>{{ $t('Payment Type Selection') }}</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row align-items-center mb-2" v-for="type in paymentTypes" :key="type['id']" :data-code="type['code'].toUpperCase()" style="min-height: 29px;">
                    <div class="col-6">{{ type['name'] }}</div>
                    <div class="col-6 text-right">
                        <button type="button" @click.prevent="setChosenPaymentType(type)"
                                class="btn btn-primary btn-sm">{{ $t('Select') }}
                        </button>
                    </div>
                </div>
                <hr>
                <div class="mt-4">
                    <div class="d-flex justify-content-between">
                        {{ $t('Total Due') }}: <span>{{ displaySoldPrice }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        {{ $t('Total Tendered') }}: <span>{{ displayTotalPaid }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        {{ $t('Balance') }}: <span :class="balanceClass">{{ displayBalance }}</span>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-end">
                <b-button variant="secondary" class="mr-2" @click="closeModal">{{ $t('Cancel') }}</b-button>
            </div>
        </div>
    </b-modal>
</template>

<script>

import api from "../mixins/api.vue";
import Modals from "../plugins/Modals";

export default {
    mixins: [api],

    props: {
        details: {
            type: Object,
            required: true
        }
    },

    beforeMount() {
        Modals.EventBus.$on(`show::modal::${this.modalId}`, (data) => {
            this.loadPaymentTypes();
            this.selectedPaymentType = data.paymentType;
            this.$bvModal.show(this.modalId);
        })
    },

    data() {
        return {
            modalId: 'data-collection-choose-payment-type-modal',
            selectedPaymentType: null,
            paymentTypes: [],
        }
    },

    computed: {
        displaySoldPrice() {
            return this.details.total_sold_price ?? 0;
        },
        displayTotalPaid() {
            return this.details.total_paid ?? 0;
        },
        displayBalance() {
            if (this.details.total_outstanding === null) {
                return Number(this.details.total_sold_price) - Number(this.details.total_paid);
            }
            return Number(this.details.total_outstanding);
        },
        balanceClass() {
            return this.displayBalance <= 0 ? 'text-success' : 'text-danger';
        },
    },

    methods: {
        loadPaymentTypes() {
            this.apiGetPaymentTypes()
                .then(({ data }) => {
                    this.paymentTypes = data.data;
                })
                .catch(e => {
                    this.displayApiCallError(e);
                });
        },

        setChosenPaymentType(paymentType) {
            this.$bvModal.hide(this.modalId);

            Modals.EventBus.$emit(`hide::modal::${this.modalId}`, {
                paymentType,
                saveChanges: true
            });
        },

        closeModal() {
            this.$bvModal.hide(this.modalId);
        }
    }
};
</script>
