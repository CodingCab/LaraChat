<template>
    <options-modal :id="'recent-inventory-movements-modal'" :title="$t('Recent Inventory Movements')" :show-stocktake-input="false" body-class="ml-0 mr-0 pl-1 pr-1" ref="blue" size="xl" scrollable no-fade>
        <template #menu-buttons>
            <a :href="productItemMovementLink" target="_blank" class="btn btn-link text-secondary btn-sm text-uppercase small">{{ $t('See all') }}</a>
        </template>

        <template v-for="record in records">
            <inventory-movement-card :record="record" />
        </template>

        <div class="d-flex align-items-center justify-content-center" style="height:100px" v-if="!isLoading && !records.length">
            {{ $t('No records found') }}
        </div>

        <div class="row" v-if="isLoading">
            <div class="col">
                <div ref="loadingContainerOverride" style="height: 100px"></div>
            </div>
        </div>

        <div class="d-flex justify-content-end">
            <a v-if="hasNextPage" :href="productItemMovementLink" target="_blank" class="btn btn-link text-secondary btn-sm text-uppercase small">{{ $t('See more') }}</a>
        </div>

        <template #modal-footer>
            <b-button dusk="cancel-button" v-show="!isLoading" variant="secondary" class="float-right" @click="$bvModal.hide('recent-inventory-movements-modal')">
                {{ $t('Cancel') }}
            </b-button>
        </template>
    </options-modal>
</template>

<script>

import api from "../mixins/api.vue";
import loadingOverlay from '../mixins/loading-overlay';
import InventoryMovementCard from '../components/SharedComponents/InventoryMovementCard.vue';
import Modals from '../plugins/Modals.js';
import OptionsModal from "../components/OptionsModal.vue";

export default {
    components: {OptionsModal, InventoryMovementCard },

    mixins: [api, loadingOverlay],

    // watch: {
    //     inventory_id() {
    //         this.loadRecords()
    //     },
    // },

    data: function() {
        return {
            records: [],
            hasNextPage: false,
            inventory_id: null,
        };
    },

    computed: {
        productItemMovementLink() {
            return '/reports/inventory-movements?filter[inventory_id]=' + this.inventory_id;
        },
    },

    beforeMount() {
        Modals.EventBus.$on('show::modal::recent-inventory-movements-modal', (data) => {
            this.inventory_id= data.inventory_id;
            this.loadRecords();
            this.$bvModal.show('recent-inventory-movements-modal');
        })
    },

    methods: {
        loadRecords: function(page = 1) {
            if (this.inventory_id == null) {
                this.notifyError(this.$t('Inventory ID is required'))
                return;
            }

            this.showLoading();
            this.records = []

            let params = {
                'select': 'id,occurred_at,type,description,quantity_before,sequence_number,custom_unique_reference_id,quantity_delta,quantity_after,warehouse_code,product_id,inventory_id,user_id',
                "filter[inventory_id]": this.inventory_id,
                include: 'product,product.productDescriptions,product.productPicture,inventory,user',
                sort: '-occurred_at,-sequence_number',
                page: page,
                per_page: 50
            };

            this.apiGetInventoryMovements(params)
                .then(({data}) => {
                    this.records = this.records.concat(data.data);
                    this.hasNextPage = data.links.next != null
                })
                .finally(() => {
                    this.hideLoading();
                });
        },
    }
}
</script>

<style>

</style>
