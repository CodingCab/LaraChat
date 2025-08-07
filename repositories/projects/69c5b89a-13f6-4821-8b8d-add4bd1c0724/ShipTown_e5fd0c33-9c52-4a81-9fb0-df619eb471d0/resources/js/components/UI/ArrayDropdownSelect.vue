<template>
    <div class="dropdown">
        <button :id="id" class="btn btn-sm dropdown-toggle text-primary font-weight-bold p-0" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span v-if="!itemSelected && placeholder">
                {{ $t(placeholder) }}
            </span>
            <span v-else>
                {{ itemSelected }}
            </span>
        </button>
        <div class="dropdown-menu" :class="alignMenuRight ? 'dropdown-menu-right' : ''">
            <a :id=" id + '-option-' + index" class="dropdown-item" v-for="(item, index) in items" :key="objectKey ? item[objectKey] : item" @click.prevent="$emit('item-selected', item)">
                <span v-if="objectKey !== ''">
                    {{ item[objectKey] }}
                </span>
                <span v-else>
                    {{ item }}
                </span>
            </a>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        id: {
            type: String,
            required: false
        },
        items: {
            type: Array,
            required: true
        },
        itemSelected: {
            type: String|Number,
            required: true
        },
        alignMenuRight: {
            type: Boolean,
            default: false
        },
        objectKey: {
            type: String,
            default: '',
            required: false
        },
        placeholder: {
            type: String,
            default: 'Select an option',
            required: false
        },
    },
}
</script>

<style scoped>
    a.dropdown-item {
        cursor: pointer;
    }
</style>
