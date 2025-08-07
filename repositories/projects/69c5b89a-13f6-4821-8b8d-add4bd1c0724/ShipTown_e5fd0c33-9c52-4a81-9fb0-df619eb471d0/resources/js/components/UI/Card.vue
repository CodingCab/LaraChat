<template>
    <div :class="cardClass">
        <div :class="bodyPadding">
            <slot/>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        cardClass: {
            type: String,
            default: 'card mb-5'
        },
        bodyClass: {
            type: String,
            default: 'card-body'
        },
        margin: {
            type: String,
            default: 'mb-5'
        },
        bodyPadding: {
            type: String,
            default: 'px-2 py-2'
        }
    }
}
</script>
