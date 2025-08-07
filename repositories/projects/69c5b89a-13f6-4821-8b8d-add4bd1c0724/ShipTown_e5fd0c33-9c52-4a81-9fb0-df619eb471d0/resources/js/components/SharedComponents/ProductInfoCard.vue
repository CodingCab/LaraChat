<template>
    <div :dusk="`product-info-card-${product.id}`">
        <div class="d-flex items-center mb-1">
            <img v-if="product.product_picture_url" :src="product.product_picture_url" class="mr-2" width="50" height="50"
                alt="Product photo">
            <img v-else src="/img/placeholder.png" class="mr-2" width="50" height="50" alt="Product photo">
            <div>
                <div v-if="name_ordered" class="text-primary h5">{{ name_ordered  }}</div>
                <div v-else class="text-primary h5">{{  product ? product['name'] : '&nbsp;' }}</div>
                <div>
                    <div v-if="sku_ordered">{{ $t('sku_ordered') }}: {{ sku_ordered }}</div>
                    {{ $t('sku') }}:
                    <font-awesome-icon icon="copy" class="fa-xs btn-link" role="button"
                        @click="copyToClipBoard((product ? product['sku'] : ''))"></font-awesome-icon>
                    <strong>&nbsp;<product-sku-button :product_sku="product['sku']" /></strong>
                </div>
            </div>
        </div>
        <div v-if="showTags && product">
            <a @click="openTagModal" :dusk="`edit-tags-product-${product.id}`" style="position: relative; top: 2px;">
                <font-awesome-icon icon="edit" class="fa-sm cursor-pointer btn-outline-primary"></font-awesome-icon>
            </a>
            <template v-if="distinctModelTags.length">
                <a v-for="tag in distinctModelTagsNotInTags" :key="'modeltag-' + tag.id" class="badge text-uppercase btn btn-outline-primary mr-1">
                    {{ tag.tag_name }}
                </a>
            </template>
            <template v-else>
                <a v-for="tag in distinctTagsNotInModelTags" :key="'tag-' + tag.id" class="badge text-uppercase btn btn-outline-primary mr-1" @click.prevent="filterByTag(tag)">
                    {{ getTagName(tag) }}
                </a>
            </template>
        </div>

        <div v-if="showProductDescriptions">
            <div class="row-col mt-3" v-if="product.productDescriptions?.length" dusk="product-descriptions">
                <div class="row-col tabs-container mb-2 flex-nowrap">
                    <ul class="nav nav-tabs flex-nowrap mr-0 small">
                        <li class="nav-item" v-for="productDescription in product.productDescriptions" :key="productDescription.language_code">
                            <a class="nav-link p-0 pl-1 pr-1 pr-lg-2"
                                :class="{ 'active': productDescription.language_code === currentLanguageCode.language_code }"
                                @click.prevent="currentLanguageCode = productDescription" data-toggle="tab" href="#">
                                {{ productDescription.language_code }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link p-0 pl-1 pr-1 pr-lg-2" href="#" @click.prevent="openDescriptionModal(true)">
                                <font-awesome-icon icon="plus" class="fa-sm text-primary cursor-pointer"></font-awesome-icon>
                            </a>
                        </li>
                    </ul>
                </div>

                <template>
                    <p>
                        {{ currentLanguageCode?.description }}
                        <font-awesome-icon icon="edit" class="fa-sm text-primary cursor-pointer" @click.prevent="openDescriptionModal(false)"></font-awesome-icon>
                    </p>
                </template>
            </div>
<!--            <div v-else class="text-muted text-sm mt-3">-->
<!--                {{ $t('No description available') }}-->
<!--                <a class="" href="#" @click.prevent="openDescriptionModal(true)">-->
<!--                    {{ $t('Create one') }}-->
<!--                </a>-->
<!--            </div>-->
        </div>

        <modal-product-description
            v-if="showProductDescriptionModal"
            :product-descriptions="product.productDescriptions"
            :product-id="product.id"
            :selected-description="currentLanguageCode"
            :is-create="addNewDescription"
            @closeModal="showProductDescriptionModal = false"
            @descriptionUpdated="descriptionUpdated"
        />

        <modal-product-tags
            v-if="showProductTagsModal"
            :product-tags="product.tags"
            :product-id="product.id"
            @closeModal="showProductTagsModal = false"
            @tagsUpdated="tagsUpdated"
        />
    </div>
</template>

<script>
import helpers from "../../mixins/helpers";
import url from "../../mixins/url";
import ModalProductDescription from '../Products/ModalProductDescription.vue';
import ModalProductTags from "../Products/ModalProductTags.vue";
import ProductSkuButton from "./ProductSkuButton.vue";

export default {
    components: { ProductSkuButton, ModalProductTags, ModalProductDescription },
    mixins: [helpers, url],

    name: "ProductInfoCard",

    data() {
        return {
            currentLanguageCode: this.product.productDescriptions?.[0],
            showProductDescriptionModal: false,
            addNewDescription: false,
            showProductTagsModal: false,
        }
    },

    props: {
        name_ordered: {
            type: String,
            default: null
        },
        sku_ordered: {
            type: String,
            default: null
        },
        product: {
            type: Object,
            default: () => ({})
        },
        showTags: {
            type: Boolean,
            default: true
        },
        showProductDescriptions: {
            type: Boolean,
            default: true
        }
    },

    computed: {
        distinctTags() {
            if (!this.product || !this.product.tags) return [];
            const seen = new Set();
            return this.product.tags.filter(tag => {
                if (seen.has(tag.id)) return false;
                seen.add(tag.id);
                return true;
            });
        },
        distinctModelTags() {
            if (!this.product || !this.product.model_tags) return [];
            const seen = new Set();
            return this.product.model_tags.filter(tag => {
                if (seen.has(tag.id)) return false;
                seen.add(tag.id);
                return true;
            });
        },
        distinctTagsNotInModelTags() {
            const modelTagIds = new Set(this.distinctModelTags.map(tag => tag.id));
            return this.distinctTags.filter(tag => !modelTagIds.has(tag.id));
        },
        distinctModelTagsNotInTags() {
            const tagIds = new Set(this.distinctTags.map(tag => tag.id));
            return this.distinctModelTags.filter(tag => !tagIds.has(tag.id));
        }
    },

    methods: {
        getTagName(tag) {
            let first = tag.name instanceof Object ? Object.keys(tag.name)[0] : 'en';
            return tag.name instanceof Object ? tag.name[first ] : tag.name
        },

        filterByTag(tag) {
            this.removeUrlParameter('search');
            this.setUrlParameterAngGo('filter[product_has_tags]', this.getTagName(tag));
        },

        openDescriptionModal(addNewDescription = false) {
            this.addNewDescription = addNewDescription;
            this.showProductDescriptionModal = true;
            setTimeout(() => {
                this.$bvModal.show('product-description-modal');
            }, 500);
        },

        descriptionUpdated(data) {
            this.product.productDescriptions = data
            if (this.currentLanguageCode) {
                this.currentLanguageCode = data.find(description => description.language_code == this.currentLanguageCode.language_code)
            } else {
                this.currentLanguageCode = this.product.productDescriptions?.[0]
            }
        },

        openTagModal() {
            this.showProductTagsModal = true;
            setTimeout(() => {
                this.$bvModal.show('product-tags-modal');
            }, 500);
        },

        tagsUpdated(tags) {
            this.product.tags = tags
        }
    }
}
</script>
