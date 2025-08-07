<template>
  <div class="row">
    <div class="col">
      <barcode-input-field :input_id="'barcode-input-field'" @barcodeScanned="startPickingOrder"
                           :placeholder="$t('Scan order to start picking')"></barcode-input-field>
      <br>
<!--      <div class="alert alert-info" role="alert">-->
<!--        {{ $t('No picks found') }}-->
<!--      </div>-->
    </div>
  </div>
</template>
<script>
import BarcodeInputField from "./SharedComponents/BarcodeInputField"

export default {
  name: 'scan-order-number-page',
  components: {BarcodeInputField},
  props: {
    startPickingOrder: {}
  }
}
</script>
