<template>
    <div class="row justify-content-center vh-100">
        <div class="col-md-9 d-flex align-items-center px-5">
            <div class="embed-responsive embed-responsive-16by9">
                <iframe
                    class="embed-responsive-item"
                    src="https://www.youtube.com/embed/TMUcZQjCQIU"
                    title="How to create API key in Magento API?"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                    allowfullscreen
                ></iframe>
            </div>
        </div>
        <div class="col-md-3 d-flex align-items-center bg-white px-5">
            <div class="w-100" ref="loadingContainer" v-if="isLoading"></div>
            <div class="w-100" v-if="!isLoading">
                <div class="mb-4">
                    <h5>{{ $t('Magento 2 API Connection') }}</h5>
                </div>

                <ValidationObserver ref="form">
                    <form class="form" @submit.prevent="submit">
                        <div class="form-group">
                            <label class="form-label" for="base_url">{{ $t('Store URL') }}</label>
                            <ValidationProvider vid="base_url" name="base_url" v-slot="{ errors }">
                            <input v-model="config.base_url" :class="{
                                        'form-control': true,
                                        'is-invalid': errors.length > 0,
                                    }" id="base_url" type="url" required>
                            <div class="invalid-feedback">
                                {{ errors[0] }}
                            </div>
                            </ValidationProvider>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="consumer_key">{{ $t('Consumer Key') }}</label>
                            <ValidationProvider vid="consumer_key" name="consumer_key" v-slot="{ errors }">
                                <input v-model="config.consumer_key" id="consumer_key" type="password" required :class="{
                                    'form-control': true,
                                    'is-invalid': errors.length > 0,
                                }">
                                <div class="invalid-feedback">
                                    {{ errors[0] }}
                                </div>
                            </ValidationProvider>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="consumer_secret">{{ $t('Consumer Secret') }}</label>
                            <ValidationProvider vid="consumer_secret" name="consumer_secret" v-slot="{ errors }">
                                <input v-model="config.consumer_secret" id="consumer_secret" type="password" required :class="{
                                    'form-control': true,
                                    'is-invalid': errors.length > 0,
                                }">
                                <div class="invalid-feedback">
                                    {{ errors[0] }}
                                </div>
                            </ValidationProvider>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="api_access_token">{{ $t('Access Token') }}</label>
                            <ValidationProvider vid="api_access_token" name="api_access_token" v-slot="{ errors }">
                                <input v-model="config.api_access_token" id="api_access_token" type="password" required :class="{
                                    'form-control': true,
                                    'is-invalid': errors.length > 0,
                                }">
                                <div class="invalid-feedback">
                                    {{ errors[0] }}
                                </div>
                            </ValidationProvider>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="api_access_token">{{ $t('Access Token Secret') }}</label>
                            <ValidationProvider vid="access_token_secret" name="access_token_secret" v-slot="{ errors }">
                                <input v-model="config.access_token_secret" id="access_token_secret" type="password" required :class="{
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
import { ValidationObserver, ValidationProvider } from "vee-validate";
import Loading from "../mixins/loading-overlay";

export default {
    mixins: [api, Loading],
    components: {
        ValidationObserver,
        ValidationProvider
    },
    data() {
        return {
            config: {
                base_url: '',
                consumer_key: '',
                consumer_secret: '',
                api_access_token: '',
                access_token_secret: '',
            },
        };
    },
    mounted() {},
    methods: {
        submit() {
            this.showLoading();

            this.apiPostMagentoApiConnection({...this.config})
                .then(({ data }) => {
                    this.$snotify.success(this.$t('Connection created.'));
                    window.location.href = '/';
                })
                .catch((error) => {
                    this.displayApiCallError(error);
                })
                .finally(this.hideLoading);
        },
    },
};
</script>
