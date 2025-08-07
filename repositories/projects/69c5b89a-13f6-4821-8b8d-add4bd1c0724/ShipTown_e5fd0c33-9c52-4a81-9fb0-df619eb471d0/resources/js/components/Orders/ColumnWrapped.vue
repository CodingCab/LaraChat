<template>
  <div class="d-flex justify-content-between flex-wrap">
    <div :class="leftClass" style="min-width: 60px;"><slot name="left-text">{{ leftText }}</slot></div>
    <div :class="'flex-fill ' + rightClass"><slot name="right-text">{{ rightText }}</slot><slot></slot></div>
  </div>
</template>
<script>
export default {
  name: 'column-wrapped',
  props: {
      leftClass: {
        type: String,
        default: 'text-left text-nowrap mr-2'
      },
      rightClass: {
        type: String,
        default: 'text-right font-weight-bold'
      },
      rightText: '',
      leftText: ''
  }
}
</script>
