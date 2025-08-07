<template>
    <div>
        <a dusk="startAutopilotButton" type="button" class="btn btn-primary col" :href="'?filter[order.order_number]='">{{ $t('Scan order manually') }}</a>
      <div v-for="bookmark in bookmarks" class="col-12 mt-1">
        <a dusk="startAutopilotButton" type="button" class="btn btn-primary col" :href="bookmark['url']">{{ bookmark['name'] }}</a>
      </div>
    </div>
</template>
<script>
export default {
  name: 'picklist-shortcuts-list',
  props: {
    bookmarks: {}
  }
}
</script>
