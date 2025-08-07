<template>
    <b-modal body-class="ml-0 mr-0 p-0" :id="modalId" size="md" scrollable no-fade hide-header hide-footer>
        <div class="card card-default mb-0">
            <div class="card-header">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span>{{ $t('Receipt Preview') }}</span>
                </div>
            </div>
            <div class="card-body">
                <div class="receipt-wrapper" v-html="receiptHtml"></div>

                <hr class="mt4">

                <div class="row mt-4 d-flex justify-content-end">
                    <b-button variant="primary" class="mr-2" @click="closeModal">{{ $t('Close preview') }}</b-button>
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
            this.receiptHtml = data.receiptHtml;
            this.$bvModal.show(this.modalId);
        })
    },

    data() {
        return {
            modalId: 'data-collection-preview-transaction-receipt-modal',
            receiptHtml: null,
        }
    },

    methods: {
        closeModal() {
            this.$bvModal.hide(this.modalId);
        }
    }
};
</script>

<style scoped lang="scss">
.card-body {
    .receipt-wrapper {
        margin: 0 auto;
        max-width: 400px;
        font-family: monospace;
    }

    &::v-deep(svg) {
        display: block;
        margin: 0 auto 20px;
    }

    &::v-deep(br) {
        content: '';
        display: block;
        height: 8px;
    }

    &::v-deep(table) {
        width: 100%;
        font-size: 12px;

        thead th,
        tbody td {
            padding: 4px;

            &:last-of-type {
                text-align: right;
            }
        }
    }

    &::v-deep(.center) {
        font-size: 12px;
        text-align: center;
    }

    &::v-deep(.font-big) {
        font-size: 24px;
        line-height: 1.2;
    }

    &::v-deep(.font-normal) {
        font-size: 12px;
        line-height: 1.2;
    }

    &::v-deep(.text-right) {
        text-align: right;
    }

    &::v-deep(.summary) {
        display: flex;
        font-size: 12px;

        span {
            &:first-of-type {
                flex: 1;
            }

            &:last-of-type {
                flex: 0 0 100px;
                max-width: 100px;
            }
        }
    }

    &::v-deep(a) {
        color: #212529;
    }
}
</style>
