<template>
    <b-modal body-class="ml-0 mr-0 pl-1 pr-1" :id="modalId" @hidden="emitNotification" size="md" scrollable no-fade>
        <template #modal-header>
            <span>{{ $t('New Fakturowo Configuration') }}</span>
        </template>

        <div class="container">
            <input id="connectionCode" type="text" v-model="newConfiguration.connection_code" class="form-control mb-2"
                   :placeholder="$t('Connection code')" required>
            <input id="apiUrl" type="text" v-model="newConfiguration.api_url" class="form-control mb-2"
                   :placeholder="$t('API URL')">
            <input id="apiKey" type="text" v-model="newConfiguration.api_key" class="form-control mb-2"
                   :placeholder="$t('API key')" required>
        </div>

        <template #modal-footer>
            <b-button variant="secondary" class="float-right" @click="$bvModal.hide(modalId);">
                {{ $t('Cancel') }}
            </b-button>
            <b-button variant="primary" class="float-right" @click="createNewConfiguration">
                {{ $t('Create') }}
            </b-button>
        </template>
    </b-modal>
</template>

<script>

import api from "../../../../../../resources/js/mixins/api.vue";
import Modals from "../../../../../../resources/js/plugins/Modals";

export default {
    mixins: [api],

    data() {
        return {
            newConfiguration: {
                connection_code: '',
                api_url: '',
                api_key: 0,
            },
            modalId: 'module-fakturowo-new-configuration-modal',
            discount: undefined,
        }
    },

    beforeMount() {
        Modals.EventBus.$on(`show::modal::${this.modalId}`, (data) => {
            this.configuration = data['configuration'];

            this.newConfiguration = {
                connection_code: '',
                api_url: '',
                api_key: 0,
            };

            if (this.configuration) {
                this.newConfiguration.connection_code = this.configuration.connection_code;
                this.newConfiguration.api_url = this.configuration.api_url;
            }

            this.$bvModal.show(this.modalId);
        })
    },

    methods: {
        createNewConfiguration() {
            this.apiPostFakturowoConfiguration(this.newConfiguration)
                .then(() => {
                    this.$bvModal.hide(this.modalId);
                    this.$snotify.success(this.$t('Fakturowo configuration created'));
                })
                .catch(error => {
                    this.displayApiCallError(error);
                })
        },

        emitNotification() {
            Modals.EventBus.$emit(`hide::modal::${this.modalId}`, this.newConfiguration);
        }
    }
};

</script>
