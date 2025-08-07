<template>
  <hr v-if="menu.name === 'separator'" class="mt-2" :key="`separator-${index}`"/>
  <a v-else :id="menu.id" class="dropdown-item text-white lightHover" :href="menu.url" :key="menu.key">{{ menu.name
    }}</a>
</template>
<script>
export default {
  name: 'reports-dropdown-item',
  props: {
    index: {},
    menu: {}
  }
}
</script>
<style>
.responsive-text a {
  font-size: 10pt;
}

@media (max-width: 768px) {
  .responsive-text a {
    font-size: 9pt;
  }
}
</style>