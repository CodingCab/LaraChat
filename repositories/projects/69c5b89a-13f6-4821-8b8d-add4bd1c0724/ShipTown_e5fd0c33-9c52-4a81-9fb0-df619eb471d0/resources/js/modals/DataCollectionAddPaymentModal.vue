<template>
    <b-modal body-class="ml-0 mr-0 p-0" :id="modalId" size="md" scrollable no-fade
             hide-header hide-footer>
        <div class="card card-default mb-0">
            <div class="card-header">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span>{{ $t('Add Payment') }}</span>
                </div>
            </div>
            <div class="card-body">
                <div class="input-group mb-3">
                    <input id="transaction_payment_amount"
                           tabindex="0"
                           v-model="amount"
                           type="number"
                           inputmode="numeric"
                           enterkeyhint="done"
                           min="0"
                           step="0.01"
                           class="form-control text-center"
                           @keyup.enter="closeModal(true)"
                    >
                </div>

                <hr class="mt4">

                <div class="row mt-4 d-flex justify-content-end">
                    <b-button variant="secondary" class="mr-2" @click="closeModal(false)">{{ $t('Cancel') }}</b-button>
                    <b-button variant="primary" @click="closeModal(true)" data-save-amount>{{ $t('Save') }}</b-button>
                </div>
            </div>
        </div>
    </b-modal>
</template>

<script>

import api from "../mixins/api.vue";
import Modals from "../plugins/Modals";

export default {
    mixins: [api],

    beforeMount() {
        Modals.EventBus.$on(`show::modal::${this.modalId}`, (data) => {
            this.$bvModal.show(this.modalId);
        })
    },

    data() {
        return {
            modalId: 'data-collection-add-payment-modal',
            amount: 0
        }
    },

    methods: {
        closeModal(saveChanges) {
            this.$bvModal.hide(this.modalId);

            Modals.EventBus.$emit(`hide::modal::${this.modalId}`, {
                amount: this.amount,
                saveChanges
            });

            this.amount = 0;
        }
    },
};
</script>
