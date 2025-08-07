<template>
  <div class="row mb-2">
    <div class="col">
      <div class="setting-title">{{ title }}</div>
      <div class="setting-desc"><slot></slot></div>
    </div>
    <div class="custom-control custom-switch m-auto text-right align-content-center float-right w-auto">
      <input type="checkbox" class="custom-control-input" @change="toggleSingleScanMode"  v-model="model">
      <label class="custom-control-label"></label>
    </div>
  </div>
</template>
<script>
export default {
  name: 'toggle',
  props: {
    title: {
      type: String,
      default: ''
    },
    model: {
      type: Boolean,
      default: false
    }
  }
}
</script>
