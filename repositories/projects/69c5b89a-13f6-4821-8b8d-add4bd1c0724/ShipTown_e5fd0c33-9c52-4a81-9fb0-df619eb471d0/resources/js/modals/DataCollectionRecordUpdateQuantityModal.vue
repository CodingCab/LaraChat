<template>
    <b-modal body-class="ml-0 mr-0 p-0" :id="modalId" size="sm" scrollable no-fade
             hide-header hide-footer>
        <div class="card card-default mb-0">
            <div class="card-header">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span>{{ $t('Update Quantity') }}</span>
                </div>
            </div>
            <div class="card-body">
                <div class="input-group mb-3">
                    <input id="record_quantity"
                           tabindex="0"
                           v-model="quantity"
                           type="number"
                           inputmode="numeric"
                           min="0"
                           step="1"
                           class="form-control text-center"
                           @keydown.enter="closeModal(true)"
                    >
                </div>

                <hr class="mt4">

                <div class="row mt-4 d-flex justify-content-end">
                    <b-button variant="secondary" class="mr-2" @click="hide">{{ $t('Cancel') }}</b-button>
                    <b-button variant="primary" @click="closeModal(true)">{{ $t('Save') }}</b-button>
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
            if (typeof data.details !== 'undefined' && typeof data.details.quantity_scanned !== 'undefined') {
                this.quantity = data.details.quantity_scanned;
                this.dataCollectionRecordId = data.details.id;
            }

            this.setFocusElementById('record_quantity');
        })
    },

    data() {
        return {
            modalId: 'data-collection-record-update-quantity-modal',
            quantity: 0,
            dataCollectionRecordId: null
        }
    },

    methods: {
        hide() {
            this.$bvModal.hide(this.modalId)
        },

        closeModal(saveChanges = false) {
            this.$bvModal.hide(this.modalId);

            Modals.EventBus.$emit(`hide::modal::${this.modalId}`, {
                quantity: this.quantity,
                id: this.dataCollectionRecordId,
                saveChanges
            });
        }
    }
};
</script>
