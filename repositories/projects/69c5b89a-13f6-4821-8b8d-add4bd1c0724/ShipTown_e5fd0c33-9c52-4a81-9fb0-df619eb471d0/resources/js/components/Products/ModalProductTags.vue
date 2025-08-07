<template>
    <b-modal
        :title="$t('Product Tags')"
        id="product-tags-modal"
        no-fade
        no-close-on-backdrop
    >
        <b-form-tags input-id="tags-product" ref="tagsProduct" v-model="tags" class="mb-2" autofocus></b-form-tags>

        <template #modal-footer>
            <b-button variant="secondary" class="float-right" @click="closeModal" dusk="btn-cancel" :disabled="isLoading">
                {{ $t('Cancel') }}
            </b-button>
            <b-button variant="primary" class="float-right" @click="saveTags" dusk="btn-submit" :disabled="isLoading">
                {{ $t('OK') }}
            </b-button>
        </template>
    </b-modal>
</template>

<script>
import loadingOverlay from '../../mixins/loading-overlay';
import api from "../../mixins/api";
import helpers from "../../helpers";

export default {
    name: "ModalProductTags",
    mixins: [loadingOverlay, api, helpers],
    props: {
        productTags: {
            type: Array,
            required: true,
        },
        productId: {
            type: Number,
            required: true,
        },
    },
    data() {
        return {
            tags: []
        }
    },
    mounted() {
        this.tags = this.productTags.map(tag => {
            return tag.name
        });
    },
    methods: {
        closeModal() {
            this.$emit('closeModal');
            this.$bvModal.hide('product-description-modal');
        },

        saveTags() {
            this.showLoading();
            this.apiPostProductTags({
                product_id: this.productId,
                tags: this.tags,
            })
            .then(({ data }) => {
                this.$emit('tagsUpdated', data.data)
                this.notifySuccess(this.$t('Product tags updated'), false)
                this.closeModal()
            })
            .catch((e) => {
                this.notifyError(this.$t('Error updating tags'));
            })
            .finally(() => {
                this.hideLoading();
            });
        }
    }
}
</script>
