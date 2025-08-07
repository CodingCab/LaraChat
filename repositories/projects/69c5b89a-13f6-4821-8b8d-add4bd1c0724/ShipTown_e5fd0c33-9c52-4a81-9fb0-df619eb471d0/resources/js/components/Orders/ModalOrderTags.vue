<template>
    <b-modal
        :title="$t('Order Tags')"
        id="order-tags-modal"
        no-fade
        no-close-on-backdrop
    >
        <b-form-tags input-id="tags-order" ref="tagsOrder" v-model="tags" class="mb-2" tag-variant="primary" autofocus></b-form-tags>

        <div class="mt-2">
            <small class="text-muted">{{ $t('Used Tags') }}</small>
            <div class="mt-1">
                <b-button
                    v-for="tag in usedTags"
                    :key="tag"
                    size="sm"
                    variant="outline-primary"
                    class="mr-1 mb-1 text-uppercase"
                    @click="addTag(tag)"
                >{{ tag }}</b-button>
            </div>
        </div>

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
    name: "ModalOrderTags",
    mixins: [loadingOverlay, api, helpers],
    props: {
        orderTags: {
            type: Array,
            required: true,
        },
        orderId: {
            type: Number,
            required: true,
        },
    },
    data() {
        return {
            tags: [],
            usedTags: []
        }
    },
    mounted() {
        this.tags = this.orderTags.map(tag => {
            return tag.name
        });
        this.loadUsedTags();
    },
    methods: {
        closeModal() {
            this.$emit('closeModal');
            this.$bvModal.hide('order-description-modal');
        },

        loadUsedTags() {
            this.apiGetOrderTags({
                'filter[taggable_type]': 'App\\Models\\Order',
                'include': 'tag',
                'per_page': 999
            })
            .then(({data}) => {
                const names = new Set();
                data.data.forEach(taggable => {
                    let name = taggable.tag_name || (taggable.tag && taggable.tag.name && taggable.tag.name.en);
                    if (name && !names.has(name)) {
                        names.add(name);
                    }
                });
                this.usedTags = Array.from(names);
            })
            .catch((error) => {
                this.displayApiCallError(error);
            });
        },

        addTag(tag) {
            if (!this.tags.includes(tag)) {
                this.tags.push(tag);
            }
        },

        saveTags() {
            this.showLoading();
            this.apiPostOrderTags({
                order_id: this.orderId,
                tags: this.tags,
            })
            .then(({ data }) => {
                this.$emit('tagsUpdated', data.data)
                this.notifySuccess(this.$t('Order tags updated'), false)
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
