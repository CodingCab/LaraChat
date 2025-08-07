<template>
    <div>
        <b-modal :id="id" ref="filtersModal" :size="size" no-fade hide-header hide-footer @shown="setFocusElementById('stocktake-input')" @hidden="modalHidden">
            <div style="position: relative; top: -10px; ">
                <div class="d-flex align-items-right small text-uppercase small text-secondary text-nowrap" style="border-bottom: 1px solid var(--bs-secondary);">
                    <div class="d-inline flex-fill font-weight-bold fa-pull-left text-uppercase small text-secondary align-content-center">{{ $t(title) }}</div>
                    <slot name="menu-buttons"></slot>
                    <button class="btn btn-sm btn-close" @click="$bvModal.hide(id)" :id="'btn-close-modal-' + id">X</button>
                </div>
                <hr class="mt-0 mb-2"/>
                <stocktake-input v-if="showStocktakeInput" v-bind:auto-focus-after="100" class="mt-1 mb-3"></stocktake-input>
                <slot></slot>
            </div>
        </b-modal>
    </div>
</template>
<script>
import api from "../mixins/api.vue";
import helpers from "../helpers";

export default {
    name: 'options-modal',
    mixins: [api, helpers],
    props:
    {
        title: {
            default: 'Options'
        },
        id: {
            type: String,
            default: 'optionsModal'
        },
        showStocktakeInput: {
            type: Boolean,
            default: true
        },
        size: {
            type: String,
            default: 'lg'
        },
    },
    methods: {
        modalHidden() {
            this.setFocusElementById('barcode-input');
            this.$emit('hidden');
        },
    }
}
</script>
