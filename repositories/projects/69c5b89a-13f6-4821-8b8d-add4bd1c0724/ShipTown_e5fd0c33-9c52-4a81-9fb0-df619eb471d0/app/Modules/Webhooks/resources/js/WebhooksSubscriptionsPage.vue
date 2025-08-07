<template>
    <div>
        <div class="card card-default">
            <div class="card-header">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span>
                        Webhooks Subscriptions (AWS SNS)
                    </span>
                    <a tabindex="-1" class="action-link" @click="showAddSubscriptionModal">
                        Add
                    </a>
                </div>
            </div>

            <div class="card-body">
                <table v-if="subscriptions.length" class="table table-borderless table-responsive mb-0">
                    <thead>
                        <tr>
<!--                            <th>ARN</th>-->
<!--                            <th>Endpoint</th>-->
                        </tr>
                    </thead>
                    <tbody>
                    <template v-for="subscription in sortedArray">
                        <tr>
                            <td>
                                <div class="font-weight-bold">
                                    {{ subscription['Endpoint'] }}
                                </div>
                                <div class="small text-secondary">
                                    {{ subscription['SubscriptionArn'] }}
                                </div>
                            </td>
                        </tr>
                    </template>
                    </tbody>
                </table>

                <p v-else class="mb-0">
                    No Subscriptions found
                </p>
            </div>
        </div>

        <!-- The modal -->
        <b-modal ref="updateFormModal" id="new-subscription-modal" title="Subscribe" @ok="handleModalOk">

            <div class="row">Required</div>
            <div class="row">
                <div class="col-2 align-content-center">
                    https://
                </div>
                <div class="col">
                    <input class="form-control" :placeholder="'URL'"
                           id="input-url"
                           type="url"
                           v-model="url"
                           inputmode="numeric"
                           required
                    />
                </div>
            </div>
            <br>

            <span>Optional</span>
            <input class="form-control" :placeholder="'Username'"
                   id="input-username"
                   type="text"
                   v-model="username"
            />

            <input class="form-control" :placeholder="'Password'"
                   id="input-password"
                   type="password"
                   v-model="password"
            />
        </b-modal>
    </div>
</template>

<script>
    import api from "../../../../../resources/js/mixins/api";
    import helpers from "../../../../../resources/js/mixins/helpers";

    export default {

        mixins: [api, helpers],

        data: () => ({
            username: '',
            password: '',
            url: '',
            subscriptions: {},
        }),

        mounted() {
            this.getConfiguration();
        },

        computed: {
            finalEndpointUrl() {
                if ((this.username !== '') && (this.password !== '')) {
                    return 'https://' + this.username.replace('@', '%40') + ':' + this.password + '@' + this.url;
                }

                return 'https://' + this.url;
            },

            sortedArray: function() {
                function compare(a, b) {
                    if (a['SubscriptionArn'] > b['SubscriptionArn'])
                        return -1;
                    if (a['SubscriptionArn'] < b['SubscriptionArn'])
                        return 1;
                    return 0;
                }

                return this.subscriptions.sort(compare);
            }
        },

        methods: {
            getConfiguration() {
                this.apiGet('/api/modules/webhooks/subscriptions', {})
                    .then(({ data }) => {
                        this.subscriptions = data.data['response']['Subscriptions'];
                    })
                    .catch((e) => {
                        this.displayApiCallError(e);
                        this.has_configuration = false;
                    });
            },

            showAddSubscriptionModal() {
                this.$bvModal.show('new-subscription-modal');
            },

            handleModalOk(bvModalEvt) {
                bvModalEvt.preventDefault();

                this.apiPost('/api/modules/webhooks/subscriptions', {'endpoint': this.finalEndpointUrl})
                    .then(() => {
                        this.notifySuccess('Subscription requested, awaiting confirmation from url');
                        this.getConfiguration()
                        this.$bvModal.hide('new-subscription-modal');
                    })
                    .catch((e) => {
                        this.displayApiCallError(e);
                    });

                this.getConfiguration();
            },
        }
    }

</script>

<style lang="scss" scoped>
    .action-link {
        cursor: pointer;
    }
</style>
