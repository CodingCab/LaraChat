<style scoped>
    .action-link {
        cursor: pointer;
    }
</style>

<template>
    <div>
        <div v-if="tokens.length > 0">
            <div class="card card-default">
                <div class="card-header">{{ $t('Authorized Applications') }}</div>

                <div class="card-body">
                    <!-- Authorized Tokens -->
                    <table class="table table-borderless mb-0">
                        <thead>
                            <tr>
                                <th>{{ $t('Name') }}</th>
                                <th>{{ $t('Scopes') }}</th>
                                <th></th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="token in tokens">
                                <!-- Client Name -->
                                <td style="vertical-align: middle;">
                                    {{ token.client.name }}
                                </td>

                                <!-- Scopes -->
                                <td style="vertical-align: middle;">
                                    <span v-if="token.scopes.length > 0">
                                        {{ token.scopes.join(', ') }}
                                    </span>
                                </td>

                                <!-- Revoke Button -->
                                <td style="vertical-align: middle;">
                                    <a class="action-link text-danger" @click="revoke(token)">
                                        {{ $t('Revoke') }}
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import api from "../../mixins/api";

    export default {
        mixins: [api],
        /*
         * The mixins's data.
         */
        data() {
            return {
                tokens: []
            };
        },

        /**
         * Prepare the mixins (Vue 1.x).
         */
        ready() {
            this.prepareComponent();
        },

        /**
         * Prepare the mixins (Vue 2.x).
         */
        mounted() {
            this.prepareComponent();
        },

        methods: {
            /**
             * Prepare the mixins (Vue 2.x).
             */
            prepareComponent() {
                this.getTokens();
            },

            /**
             * Get all of the authorized tokens for the user.
             */
            getTokens() {
                this.apiGetOauthTokens()
                    .then(response => {
                        this.tokens = response.data;
                    });
            },
            /**
             * Revoke the given token.
             */
            revoke(token) {
                this.apiDeleteOauthToken(token)
                    .then(response => {
                        this.getTokens();
                    });
            },
        }
    }
</script>
