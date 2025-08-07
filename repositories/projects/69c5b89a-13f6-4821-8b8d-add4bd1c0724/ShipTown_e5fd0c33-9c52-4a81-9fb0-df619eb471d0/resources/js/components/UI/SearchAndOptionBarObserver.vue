<template>
    <div ref="observerElement" style="height: 1px; visibility: hidden;"></div>
</template>

<script>
export default {
    data() {
        return {
            observer: null
        }
    },
    mounted() {
        this.observer = new IntersectionObserver((entries) => {
            const isVisible = entries[0].isIntersecting;
            this.$eventBus.$emit('observer-status', isVisible);
        }, {
            root: null,
            threshold: 0.1
        });

        this.observer.observe(this.$refs.observerElement);
    },

    beforeDestroy() {
        this.observer.disconnect();
    }
}
</script>
