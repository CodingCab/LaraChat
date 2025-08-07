<template>
  <button class="btn btn-primary btn-block" @click="click"><slot></slot></button>
</template>
<script>
export default {
  name: 'modal-button',
    props: {
        href: {
            type: String,
            default: null
        }
    },
  methods: {
    click(data) {
        if (this.href) {
            if (this.href === this.$router.currentRoute.path) {
                this.$router.go();
            } else {
                this.$router.push(this.href);
            }
        } else {
            this.$emit('click', data);
        }
    }
  }
}
</script>
<style scoped>

::-webkit-scrollbar {
  height: 4px;
  width: 4px;
}

::-webkit-scrollbar-thumb:horizontal {
  background: lightgray;
  border-radius: 10px;
}

::-webkit-scrollbar:horizontal {
  height: 8px;
  background: none;
}

</style>
