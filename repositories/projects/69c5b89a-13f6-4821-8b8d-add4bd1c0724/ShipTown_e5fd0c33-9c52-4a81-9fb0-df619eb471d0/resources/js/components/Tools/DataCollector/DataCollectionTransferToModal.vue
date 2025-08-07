<template>
  <b-modal id="transferToModal" no-fade hide-header @hidden="setFocusElementById('barcode_input')">
    <template v-for="warehouse in warehouses">
      <button v-if="dataCollection && warehouse['id'] !== dataCollection['warehouse_id']"
              @click.prevent="transferToWarehouse" v-b-toggle class="col btn mb-2 btn-primary" :key="warehouse.id">{{
        warehouse.name }}
      </button>
    </template>

    <template #modal-footer>
      <b-button variant="secondary" class="float-right" @click="$bvModal.hide('transferToModal');">Cancel
      </b-button>
    </template>
  </b-modal>
</template>
<script>
import helpers from "../../../helpers";

export default {
  name: 'data-collection-transfer-to-modal',
    mixins: [helpers],
  props: {
    dataCollection: {},
    transferToWarehouse: {},
    warehouses: {}
  }
}
</script>
