<template>
    <div>
      <div class="text-primary">
        {{ $t('Shipping Address') }}
      </div>
      <div class="text-secondary small">
        {{ dataCollection['shipping_address']['first_name'] ?? '' }}
        {{ dataCollection['shipping_address']['last_name'] ?? '' }}
        <br>
        {{ dataCollection['shipping_address']['company'] ?? '' }}
        <br>
        {{ dataCollection['shipping_address']['address1'] ?? '' }}
        <br>
        {{ dataCollection['shipping_address']['address2'] ?? '' }}
        <br>
        {{ dataCollection['shipping_address']['city'] ?? '' }}
        {{ dataCollection['shipping_address']['postcode'] ?? '' }}
        <br>
        {{ dataCollection['shipping_address']['country_name'] ?? '' }}
      </div>
    </div>
</template>
<script>
export default {
  name: 'shipping-address',
  props: {
    dataCollection: {}
  }
}
</script>
