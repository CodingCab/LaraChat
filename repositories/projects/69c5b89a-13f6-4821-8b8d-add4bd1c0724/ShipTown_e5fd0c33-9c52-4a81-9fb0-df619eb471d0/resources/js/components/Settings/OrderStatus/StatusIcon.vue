<template>
    <font-awesome-icon
        :icon="status ? 'check-circle' : 'times-circle'"
        :class="status ? 'text-success' : 'text-danger'"
    >
    </font-awesome-icon>
</template>

<script>
export default {
  props: {
      status: {
          required: true,
          type: Boolean
      }
  },
};
</script>

<style>
</style>
