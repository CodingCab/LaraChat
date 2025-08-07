<template>
    <div class="text-center border-0 d-inline-block p-0 px-2 m-0 align-text-top align-top h-100" :style="{ minWidth: minWidth}" :id="id">
        <div class="small text-secondary align-text-top align-top">
            <small class="small text-secondary">{{ this.label }}</small>
        </div>
        <div class="">
            <h4 v-if="this.text">{{ this.text || '&nbsp;'}}</h4>
            <slot></slot>
        </div>
    </div>
</template>

<script>
    export default {
        name: "TextCard",
        props: {
            label: null,
            text: null,
            id: {
                type: String,
            },
            minWidth: {
                type: String,
                default: '70px'
            }
        },
    }
</script>

<style scoped>

</style>
