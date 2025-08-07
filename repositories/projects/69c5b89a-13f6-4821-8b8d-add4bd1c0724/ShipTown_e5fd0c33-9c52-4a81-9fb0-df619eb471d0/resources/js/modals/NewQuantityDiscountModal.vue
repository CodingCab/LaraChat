<template>
    <b-modal body-class="ml-0 mr-0 pl-1 pr-1" :id="modal_id" @hidden="emitNotification" size="xl" scrollable no-fade>
        <template #modal-header>
            <span>{{ $t('New Quantity Discount') }}</span>
        </template>

        <div class="container">
            <input id="discountName" type="text" :disabled="!isCreatingNewDiscount" v-model="newDiscount.name"
                   class="form-control mb-2" :placeholder="$t('Discount name')" required>
            <select name="discountType" id="discountType" :disabled="!isCreatingNewDiscount"
                    v-model="newDiscount.job_class"
                    class="form-control mb-2" required>
                <option value="">-</option>
                <option v-for="(type, index) in discountTypes" :value="type.jobClass" :key="`type${index}`">
                    {{ type.name }}
                </option>
            </select>
        </div>

        <template #modal-footer>
            <b-button variant="secondary" class="float-right" @click="$bvModal.hide(modal_id);">
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
                name: '',
                job_class: '',
            },
            modal_id: 'new-quantity-discount-modal',
            discount: undefined,
            discountTypes: [
                {
                    name: this.$t('Buy X, get Y for Z price'),
                    jobClass: 'App\\Modules\\DataCollectorQuantityDiscounts\\src\\Jobs\\BuyXGetYForZPriceDiscount'
                },
                {
                    name: this.$t('Buy X, get Y for Z percent discount'),
                    jobClass: 'App\\Modules\\DataCollectorQuantityDiscounts\\src\\Jobs\\BuyXGetYForZPercentDiscount'
                },
                {
                    name: this.$t('Buy X for Y price'),
                    jobClass: 'App\\Modules\\DataCollectorQuantityDiscounts\\src\\Jobs\\BuyXForYPriceDiscount'
                },
                {
                    name: this.$t('Buy X for Y percent discount'),
                    jobClass: 'App\\Modules\\DataCollectorQuantityDiscounts\\src\\Jobs\\BuyXForYPercentDiscount'
                },
                {
                    name: this.$t('VolumePurchase percent discount'),
                    jobClass: 'App\\Modules\\DataCollectorQuantityDiscounts\\src\\Jobs\\VolumePurchasePercentDiscount'
                },
                {
                    name: this.$t('VolumePurchase price discount'),
                    jobClass: 'App\\Modules\\DataCollectorQuantityDiscounts\\src\\Jobs\\VolumePurchasePriceDiscount'
                }
            ],
        }
    },

    beforeMount() {
        Modals.EventBus.$on(`show::modal::${this.modal_id}`, (data) => {
            this.discount = data['discount'];

            this.newDiscount = {
                name: '',
                job_class: '',
            };

            if (this.discount) {
                this.newDiscount.name = this.discount.name;
                this.newDiscount.job_class = this.discount.job_class;
            }

            this.$bvModal.show(this.modal_id);
        })
    },

    computed: {
        isCreatingNewDiscount() {
            return this.discount === null || (this.discount === undefined);
        }
    },

    methods: {
        createNewDiscount() {
            this.apiPostQuantityDiscount(this.newDiscount)
                .then(({data}) => {
                    this.$bvModal.hide(this.modal_id);
                    if (typeof data.data !== 'undefined' && typeof data.data.id !== 'undefined') {
                        window.location.href = '/settings/modules/quantity-discounts/' + data.data.id;
                    }
                })
                .catch(error => {
                    this.displayApiCallError(error);
                })
        },

        emitNotification() {
            Modals.EventBus.$emit(`hide::modal::${this.modal_id}`, this.newProduct);
        }
    }
};

</script>
