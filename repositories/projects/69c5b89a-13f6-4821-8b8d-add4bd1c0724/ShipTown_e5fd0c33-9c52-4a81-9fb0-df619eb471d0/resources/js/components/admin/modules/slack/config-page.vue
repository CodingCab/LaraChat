<template>
    <div>
        <div class="card card-default">
            <div class="card-header">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span>
                        {{ $t('Slack Incoming Webhook') }}
                    </span>
                    <a href="https://api.slack.com/messaging/webhooks#posting_with_webhooks" target="_blank">{{ $t('Slack Docs') }}</a>
                </div>
            </div>

            <div class="card-body text-center">
                <p v-if="config && config.incoming_webhook_url === null" class="mb-0 text-center">
                    <input placeholder="Webhook URL"
                           class="form-control"
                           v-model="new_incoming_webhook_url"
                           autocomplete="off"
                           dusk="incoming_webhook_url_field"
                           @keyup.enter="saveIncomingWebhook"
                    />
                    <b-button variant="primary" class="mt-3 fa-pull-right" @click="saveIncomingWebhook">{{ $t('CONNECT') }}</b-button>
                </p>
                <p v-if="config && config.incoming_webhook_url !== null" >
                    <b-button variant="danger" class="mt-2" @click="disconnectWebhook">{{ $t('DISCONNECT') }}</b-button>
                </p>
            </div>
        </div>
    </div>
</template>

<script>
    import { BModal, VBModal, BButton } from 'bootstrap-vue';

    import RMSApiConfigurationForm from '../../../Settings/RmsapiConfigurationForm';
    import api from "../../../../mixins/api";

    export default {
        mixins: [api],

        components: {
            'b-modal': BModal,
            'b-button': BButton,
            'rms-api-configuration-form': RMSApiConfigurationForm,
        },

        directives: {
            'b-modal': VBModal
        },

        data: () => ({
            config: null,
            new_incoming_webhook_url: ''
        }),

        mounted() {
            this.loadConfiguration();
        },

        methods: {
            saveIncomingWebhook() {
                this.apiPostModulesSlackConfig({
                        incoming_webhook_url: this.new_incoming_webhook_url
                    })
                    .catch(({ response }) => {
                        this.displayApiCallError(response.data.message);
                    })
                    .finally(() => {
                        this.loadConfiguration();
                    });

            },

            disconnectWebhook() {
                this.apiPostModulesSlackConfig({
                        incoming_webhook_url: null
                    })
                    .catch(({ response }) => {
                        this.displayApiCallError(response.data.message);
                    })
                    .finally(() => {
                        this.loadConfiguration();
                    });

            },

            loadConfiguration() {
                this.apiGetModulesSlackIncomingWebhook({})
                    .then(({ data }) => {
                        this.config = data.data;
                    })
                    .catch(({ response }) => {
                        this.displayApiCallError(response);
                    })
            },
        },
    }

</script>

<style lang="scss" scoped>
    .action-link {
        cursor: pointer;
    }
</style>
