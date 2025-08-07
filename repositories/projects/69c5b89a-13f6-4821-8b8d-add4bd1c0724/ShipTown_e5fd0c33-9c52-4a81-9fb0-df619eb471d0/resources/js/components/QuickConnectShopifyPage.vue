<template>
    <div class="row justify-content-center vh-100">
<!--        <div class="col-12 px-5">-->
<!--            <breadcrumbs></breadcrumbs>-->
<!--        </div>-->
        <div class="col-md-9 d-flex align-items-center px-5">
            <embeded-youtube-video :video_url="video_url" title="How to import orders from Shopify?"/>
        </div>
        <div class="col-md-3 d-flex align-items-center bg-white px-5">
            <div class="w-100">
                <div class="mb-4">
                    <h4>{{ $t('Connect Shopify') }}</h4>
                </div>

                <ValidationObserver ref="form">
                    <form class="form" @submit.prevent="submit" ref="loadingContainer">

                        <div class="form-group">
                            <label class="form-label" for="store_url">{{ $t('Store URL') }}</label>
                            <ValidationProvider vid="store_url" name="store_url" v-slot="{ errors }">
                            <input v-model="config.store_url" id="store_url" type="url" required :class="{
                                'form-control': true,
                                'is-invalid': errors.length > 0,
                            }">
                            <div class="invalid-feedback">
                                {{ errors[0] }}
                            </div>
                            </ValidationProvider>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="shopify_access_token">{{ $t('Admin API access token') }}</label>
                            <ValidationProvider vid="shopify_access_token" name="shopify_access_token" v-slot="{ errors }">
                                <input v-model="config.shopify_access_token" id="shopify_access_token" type="text" required :class="{
                                    'form-control': true,
                                    'is-invalid': errors.length > 0,
                                }">
                                <div class="invalid-feedback">
                                    {{ errors[0] }}
                                </div>
                            </ValidationProvider>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="shopify_api_key">{{ $t('API key') }}</label>
                            <ValidationProvider vid="shopify_api_key" name="shopify_api_key" v-slot="{ errors }">
                                <input v-model="config.shopify_api_key" id="shopify_api_key" type="text" required :class="{
                                    'form-control': true,
                                    'is-invalid': errors.length > 0,
                                }">
                                <div class="invalid-feedback">
                                    {{ errors[0] }}
                                </div>
                            </ValidationProvider>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="shopify_api_password">{{ $t('API secret key') }}</label>
                            <ValidationProvider vid="shopify_api_password" name="shopify_api_password" v-slot="{ errors }">
                                <input v-model="config.shopify_api_password" id="shopify_api_password" type="text" required :class="{
                                    'form-control': true,
                                    'is-invalid': errors.length > 0,
                                }">
                                <div class="invalid-feedback">
                                    {{ errors[0] }}
                                </div>
                            </ValidationProvider>
                        </div>

                    </form>
                </ValidationObserver>
                <b-button @click="submit" variant="primary" class="btn btn-primary btn-block">{{ $t('Connect') }}</b-button>
            </div>
        </div>
    </div>
</template>

<script>
import api from "../mixins/api";
import {ValidationObserver, ValidationProvider} from "vee-validate";
import Loading from "../mixins/loading-overlay";
import EmbededYoutubeVideo from "./EmbededYoutubeVideo.vue";
import Breadcrumbs from "./Reports/Breadcrumbs.vue";

export default {
    mixins: [api, Loading],
    components: {
        Breadcrumbs,
        EmbededYoutubeVideo,
        ValidationObserver,
        ValidationProvider
    },
    data() {
        return {
            video_url: 'https://www.youtube.com/embed/sE4vzdyy2ic',
            config: {
                store_url: '',
                cart_id: 'Shopify',
                shopify_access_token: '',
                shopify_api_key: '',
                shopify_api_password: '',
            },
        };
    },
    mounted() {},
    methods: {
        submit() {
            this.showLoading();
            this.apiPostApi2cartConnection({...this.config})
                .then(({ data }) => {
                    this.$snotify.success(this.$t('Connection created.'));
                    window.location.href = '/';
                })
                .catch((error) => {
                    if (error.response) {
                        if (error.response.status === 422) {
                            this.$refs.form.setErrors(error.response.data.errors);
                        }
                    }
                })
                .finally(this.hideLoading);
        },
    },
};
</script>
