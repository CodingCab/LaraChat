<template>
    <options-modal id="inventory-reservations-modal" :title="$t('Inventory Reservations')" :show-stocktake-input="false" body-class="ml-0 mr-0 pl-1 pr-1" size="xl" scrollable no-fade>
        <report ref="inventory_reservations_modal_report"
                :report-url="'/reports/inventory-reservations'"
                :params="{ 'filter[inventory_id]': inventory_id, sort: '-created_at' }"
                :showOnlyDataTable="true" />
        <template #modal-footer>
            <b-button dusk="cancel-button" variant="secondary" class="float-right" @click="$bvModal.hide('inventory-reservations-modal')">
                {{ $t('Cancel') }}
            </b-button>
        </template>
    </options-modal>
</template>

<script>
import OptionsModal from '../components/OptionsModal.vue';
import Report from '../components/Reports/Report.vue';
import Modals from '../plugins/Modals.js';

export default {
    components: {OptionsModal, Report},
    data() {
        return {
            inventory_id: null,
        };
    },
    beforeMount() {
        Modals.EventBus.$on('show::modal::inventory-reservations-modal', (data) => {
            this.inventory_id = data.inventory_id;
            // this.$nextTick(() => {
            //     if (this.$refs.inventory_reservations_modal_report) {
            //         this.$refs.inventory_reservations_modal_report.reloadRecords();
            //     }
            // });
            this.$bvModal.show('inventory-reservations-modal');
        });
    }
}
</script>

<style>
</style>

