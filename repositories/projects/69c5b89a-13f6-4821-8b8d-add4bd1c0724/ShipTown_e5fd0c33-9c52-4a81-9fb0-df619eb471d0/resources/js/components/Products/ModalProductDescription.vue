<template>
    <b-modal
        :title="$t('Product Descriptions')"
        id="product-description-modal"
        no-fade
        no-close-on-backdrop
    >
        <ValidationObserver ref="form">
            <form class="form" ref="loadingContainer">
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label" for="language-code">{{ $t('Language') }}</label>
                    <div class="col-sm-9">
                        <select class="form-control" v-model="languageCode" dusk="select-language-code" :disabled="!isCreate">
                            <option v-for="language in filteredLanguageDescription" :key="language.code" :value="language.code">
                                {{ language.name }}
                            </option>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-form-label" for="description">{{ $t('Description') }}</label>
                    <div class="col-sm-9">
                        <ValidationProvider vid="description" name="description" v-slot="{ errors }">
                            <textarea v-model="description" :class="{
                                'form-control': true,
                                'is-invalid': errors.length > 0,
                            }" id="memorize-name" required></textarea>
                            <div class="invalid-feedback">{{ errors[0] }}</div>
                        </ValidationProvider>
                        <div class="mt-1">
                            <a v-if="languageCode != ''" href="#" class="text-primary" @click.prevent="generateDescription" dusk="btn-generate-description">
                                {{ $t('Generate Description with AI') }} <font-awesome-icon icon="magic" class="ml-1"></font-awesome-icon>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-form-label"></label>
                    <div class="col-sm-9">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="enable-auto-translate" dusk="enable-auto-translate"
                                v-model="enableAutoTranslate">
                            <label class="custom-control-label" for="enable-auto-translate">{{ $t('Enable auto translate') }}</label>
                        </div>
                    </div>
                </div>

                <div class="form-group row" v-if="enableAutoTranslate">
                    <label class="col-sm-3 col-form-label" for="description">{{ $t('Auto translate to') }}</label>
                    <div class="col-sm-9">
                        <div class="row">
                            <div v-for="lang in filteredAutoTranslateLanguages" class="col-sm-6" :key="lang.code">
                                <div class="custom-control custom-switch">
                                    <input
                                        type="checkbox"
                                        class="custom-control-input"
                                        :id="`translate-to${lang.code}`"
                                        :dusk="`translate-to${lang.code}`"
                                        :value="lang.code"
                                        v-model="autoTranslateTo"
                                    >
                                    <label class="custom-control-label" :for="`translate-to${lang.code}`">{{ lang.name }}</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group row" v-if="enableAutoTranslate">
                    <label class="col-sm-3 col-form-label"></label>
                    <div class="col-sm-9">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="check-all-language" dusk="check-all-language"
                                v-model="checkAllLanguage">
                            <label class="custom-control-label" for="check-all-language">{{ $t('Check All') }}</label>
                        </div>
                    </div>
                </div>
            </form>
        </ValidationObserver>

        <template #modal-footer>
            <b-button variant="secondary" class="float-right" @click="closeModal" dusk="btn-cancel" :disabled="isLoading">
                {{ $t('Cancel') }}
            </b-button>
            <b-button variant="primary" class="float-right" @click="updateOrCreateDescription" dusk="btn-submit" :disabled="isLoading">
                {{ $t('OK') }}
            </b-button>
        </template>
    </b-modal>
</template>

<script>
import { ValidationObserver, ValidationProvider } from "vee-validate";

import loadingOverlay from '../../mixins/loading-overlay';
import api from "../../mixins/api";
import helpers from "../../helpers";
import { lang } from "vue-moment";

export default {
    name: "ModalProductDescription",
    components: {
        ValidationObserver,
        ValidationProvider,
    },
    computed: {
        filteredAutoTranslateLanguages() {
            return this.availableLanguages.filter(lang => lang.code !== this.languageCode);
        },
        languageThatHasNoDescription() {
            return this.availableLanguages.filter(language => {
                return !this.productDescriptions.some(description => description.language_code === language.code);
            });
        },
        selectedOnlyLanguage() {
            return this.availableLanguages.filter(language => {
                return language.code === this.selectedDescription.language_code
            })
        },
        filteredLanguageDescription() {
            return (this.isCreate ? this.languageThatHasNoDescription : this.selectedOnlyLanguage)
        }
    },
    mixins: [loadingOverlay, api, helpers],
    props: {
        productDescriptions: {
            type: Array,
            required: true,
        },
        productId: {
            type: Number,
            required: true,
        },
        selectedDescription: {
            type: Object,
            required: false,
            default: null,
        },
        isCreate: {
            type: Boolean,
            required: false,
            default: false,
        },
    },
    data() {
        return {
            languageCode: 'en',
            description: '',
            enableAutoTranslate: false,
            autoTranslateTo: [],
            checkAllLanguage: false,
            availableLanguages: [
                { code: 'en', name: 'English' },
                { code: 'de', name: 'German' },
                { code: 'es', name: 'Spanish' },
                { code: 'fr', name: 'French' },
                { code: 'ga', name: 'Irish' },
                { code: 'hr', name: 'Croatian' },
                { code: 'it', name: 'Italian' },
                { code: 'pl', name: 'Polish' },
                { code: 'pt', name: 'Portuguese' },
            ],
        }
    },
    mounted() {
        if (!this.isCreate) {
            this.languageCode = this.selectedDescription.language_code;
            this.description = this.selectedDescription.description;
        }
    },
    watch: {
        isCreate(newValue) {
            if (newValue) {
                // remove all language from selection if existing in product descriptions
                this.languageCode = 'en';
                this.description = '';
            } else {
                // disable select language
                this.languageCode = this.selectedDescription.language_code;
                this.description = this.selectedDescription.description;
            }
        },
        checkAllLanguage(newValue) {
            if (newValue) {
                this.autoTranslateTo = this.filteredAutoTranslateLanguages.map(lang => {
                    return lang.code
                })
            }
        },
        autoTranslateTo(newValue) {
            this.checkAllLanguage = newValue.length == this.filteredAutoTranslateLanguages.length
        }
    },
    methods: {
        generateDescription() {
            this.showLoading();

            this.apiPostChatGptGenerateProductDescriptions({
                product_id: this.productId,
                language_code: this.languageCode
            })
            .then(({ data }) => {
                this.description = data.data.description
            })
            .catch(() => {
                this.notifySuccess(this.$t('Error generating description'), false);
            })
            .finally(() => {
                this.hideLoading();
            });
        },
        closeModal() {
            this.$emit('closeModal');
            this.$bvModal.hide('product-description-modal');
        },
        async updateOrCreateDescription() {
            const isValid = await this.$refs.form.validate();
            if (!isValid) return

            this.showLoading();

            this.apiPostProductDescriptions({
                product_id: this.productId,
                language_code: this.languageCode,
                description: this.description
            })
            .then(({ data }) => {
                let updatedData = [...this.productDescriptions];

                if (this.isCreate) {
                    updatedData.push(data.data);
                } else {
                    updatedData = updatedData.map(description =>
                        description.language_code === data.data.language_code
                            ? { ...description, description: data.data.description }
                            : description
                    );
                }

                this.$emit('descriptionUpdated', updatedData)
                this.notifySuccess(this.$t('Product description updated'), false)

                if (!this.enableAutoTranslate) {
                    this.closeModal()
                } else {
                    this.translateDescription(data.data)
                }
            })
            .catch((e) => {
                this.notifyError(this.$t('Error updating description'));
            })
            .finally(() => {
                this.hideLoading();
            });
        },

        translateDescription(description) {
            this.showLoading();

            this.apiPostChatGptTranslateProductDescriptions({
                product_description_id: description.id,
                auto_translate_to: this.autoTranslateTo,
            })
            .then(({ data }) => {
                this.$emit('descriptionUpdated', data.data)
                this.notifySuccess(this.$t('Product description translated'), false)

                this.closeModal()
            })
            .catch((e) => {
                this.notifyError(this.$t('Error translating description'));
            })
            .finally(() => {
                this.hideLoading();
            });
        }
    },
};
</script>

<style>
/* Add your styling here */
</style>
