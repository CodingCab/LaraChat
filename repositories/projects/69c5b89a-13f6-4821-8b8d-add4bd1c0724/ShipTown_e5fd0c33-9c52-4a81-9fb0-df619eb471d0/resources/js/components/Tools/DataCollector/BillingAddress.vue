<template>
    <div>
      <div class="text-primary">
        {{ $t('Billing Address') }}
      </div>
      <div class="text-secondary small">
        {{ dataCollection['billing_address']['first_name'] ?? '' }}
        {{ dataCollection['billing_address']['last_name'] ?? '' }}
        <br>
        {{ dataCollection['billing_address']['company'] ?? '' }}
        <br>
        {{ dataCollection['billing_address']['address1'] ?? '' }}
        <br>
        {{ dataCollection['billing_address']['address2'] ?? '' }}
        <br>
        {{ dataCollection['billing_address']['city'] ?? '' }}
        {{ dataCollection['billing_address']['postcode'] ?? '' }}
        <br>
        {{ dataCollection['billing_address']['country_name'] ?? '' }}
      </div>
    </div>
</template>
<script>
export default {
  name: 'billing-address',
  props: {
    dataCollection: {}
  }
}
</script>
