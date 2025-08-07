<template>
  <div class="text-left text-secondary font-weight-bold text-uppercase small flex-fill">
      {{ $t(breadcrumbs) }}
  </div>
</template>
<script>
import url from "../../mixins/url.vue";

export default {
    mixins: [url],
    name: 'breadcrumbs',

    data() {
        return {
            breadcrumbs: '',
        }
    },

    mounted() {
        this.breadcrumbs = this.$router.currentRoute.path
          .replace('/', '')
          .replaceAll('/', ' > ')
          .replaceAll('-', ' ');
    }
}
</script>
