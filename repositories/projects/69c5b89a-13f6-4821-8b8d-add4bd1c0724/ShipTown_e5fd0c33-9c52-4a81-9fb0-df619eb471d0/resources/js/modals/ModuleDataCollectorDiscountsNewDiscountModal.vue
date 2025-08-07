<template>
    <b-modal body-class="ml-0 mr-0 pl-1 pr-1" :id="modalId" @hidden="emitNotification" size="xl" scrollable no-fade>
        <template #modal-header>
            <span>{{ $t('New Discount') }}</span>
        </template>

        <div class="container">
            <input id="discountCode" type="text" v-model="newDiscount.code" class="form-control mb-2" :placeholder="$t('Discount code')" required>
            <input id="discountPercentageDiscount" type="number" min="0" max="100" v-model="newDiscount.percentage_discount"
                class="form-control mb-2" :placeholder="$t('Percentage discount')" required>
        </div>

        <template #modal-footer>
            <b-button variant="secondary" class="float-right" @click="$bvModal.hide(modalId);">
                {{ $t('Cancel') }}
            </b-button>
            <b-button variant="primary" class="float-right" @click="createNewDiscount">
                {{ $t('Create') }}
            </b-button>
        </template>
    </b-modal>
</template>

<script>

import api from "../mixins/api.vue";
import Modals from "../plugins/Modals";

export default {
    mixins: [api],

    data() {
        return {
            newDiscount: {
                code: '',
                percentage_discount: 0,
            },
            modalId: 'module-data-collector-discounts-new-discount-modal',
            discount: undefined,
        }
    },

    beforeMount() {
        Modals.EventBus.$on(`show::modal::${this.modalId}`, (data) => {
            this.discount = data['discount'];

            this.newDiscount = {
                name: '',
                code: '',
            };

            if (this.discount) {
                this.newDiscount.code = this.discount.code;
                this.newDiscount.percentage_discount = this.discount.percentage_discount;
            }

            this.$bvModal.show(this.modalId);
        })
    },

    computed: {
        isCreatingNewDiscount() {
            return this.discount === null || (this.discount === undefined);
        }
    },

    methods: {
        createNewDiscount() {
            this.apiPostDiscount(this.newDiscount)
                .then(({ data }) => {
                    this.$bvModal.hide(this.modalId);
                    this.$snotify.success(this.$t('Discount created'));
                })
                .catch(error => {
                    this.displayApiCallError(error);
                })
        },

        emitNotification() {
            Modals.EventBus.$emit(`hide::modal::${this.modalId}`, this.newDiscount);
        }
    }
};

</script>
