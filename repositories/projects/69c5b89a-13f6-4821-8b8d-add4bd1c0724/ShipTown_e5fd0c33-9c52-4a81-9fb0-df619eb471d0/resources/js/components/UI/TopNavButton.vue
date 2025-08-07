<template>
    <button
        type="button"
        class="btn btn-primary ml-2"
        style="width: 42px;"
        :id="buttonId"
        :dusk="dusk"
        @click="$emit('click')"
    >
        <font-awesome-icon :icon="icon" class="fa-lg"></font-awesome-icon>
    </button>
</template>

<script>

export default {
    props: {
        buttonId: {
            type: String,
            default: 'options-button'
        },
        icon: {
            type: String,
            default: 'ellipsis-vertical'
        },
        dusk: {
            type: String,
            default: null
        }
    }
}
</script>
