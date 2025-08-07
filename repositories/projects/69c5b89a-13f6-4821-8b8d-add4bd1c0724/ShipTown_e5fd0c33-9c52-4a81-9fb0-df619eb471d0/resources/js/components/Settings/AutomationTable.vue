<template>
    <div>
        <div class="alert alert-info">
            <column-wrapped :left-text="$t('Access more automations trough modules')">
                <b-btn variant="primary" size="sm" href="/settings/modules?filter%5Bsearch%5D=Automation">MODULES</b-btn>
            </column-wrapped>
        </div>
        <div class="card card-default">
            <div class="card-header">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span>
                        {{ $t('Active Orders Automations') }}
                    </span>
                    <button type="button" dusk="add-new-button" class="action-link btn btn-sm btn-light" @click="showCreateForm()">
                        {{ $t('Add New') }}
                    </button>
                </div>
            </div>

            <div class="card-body">
                <table v-if="automations.length > 0" class="table table-borderless table-responsive table-hover mb-0">
                    <thead>
                        <tr>
                            <th>{{ $t('Name') }}</th>
                            <th>{{ $t('Priority') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(automation, i) in automations" :key="i">
                            <td @click.prevent="showEditForm(automation)"><status-icon :status="automation.enabled" class="small" /> {{ automation.name }}</td>
                            <td @click.prevent="showEditForm(automation)">{{ automation.priority }}</td>
                            <td class="text-right">
                                <a @click.prevent="confirmDelete(automation)">
                                    <font-awesome-icon icon="trash"></font-awesome-icon>
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <p v-else class="mb-0">
                    {{ $t('No automations found.') }}
                </p>
            </div>
        </div>
        <!-- The modals -->
        <create-modal id="createForm" @onCreated="addAutomations" :events="events"></create-modal>
        <edit-modal id="editForm" :selectedAutomation="selectedAutomation" @onUpdated="updateAutomations" :events="events"></edit-modal>
    </div>
</template>

<script>

import CreateModal from './Automations/CreateModal';
import EditModal from './Automations/EditModal';
import StatusIcon from './Automations/StatusIcon';
import api from "../../mixins/api.vue";
import ColumnWrapped from "../Orders/ColumnWrapped.vue";

export default {
    mixins: [api],
    components: {
        ColumnWrapped,
        'create-modal': CreateModal,
        'edit-modal': EditModal,
        'status-icon': StatusIcon
    },

    mounted() {
        this.apiGetAutomations()
            .then(({ data }) => {
                this.automations = data.data;
            })
            .catch((error) => {
                this.displayApiCallError(this.$t('Error fetching automations'));
            });

        this.apiGetAutomationConfig()
            .then(({ data }) => {
                this.events = [data];
            })
            .catch((error) => {
                this.displayApiCallError(this.$t('Error fetching automations'));
            });
    },

    data: () => ({
        automations: [],
        selectedAutomation: {},
        events: [],
    }),

    methods: {
        showCreateForm(){
            $('#createForm').modal('show');
        },
        showEditForm(automation) {
            this.selectedAutomation = automation;
            $('#editForm').modal('show');
        },
        addAutomations(orderStatus){
            this.automations.push(orderStatus)
        },
        updateAutomations(newValue) {
            const indexAutomations = this.automations.findIndex(automation => automation.id == newValue.id)
            this.$set(this.automations, indexAutomations, newValue)
        },
        confirmDelete(selectedAutomation) {
            const indexAutomations = this.automations.findIndex(automation => automation.id == selectedAutomation.id)
            this.$snotify.confirm(this.$t('Once deleted, data cannot be restored'), this.$t('Are you sure?'), {
                position: 'centerCenter',
                buttons: [
                    {
                        text: this.$t('Yes'),
                        action: (toast) => {
                            this.delete(selectedAutomation.id, indexAutomations)
                            this.$snotify.remove(toast.id);
                        }
                    },
                    {text: this.$t('Cancel')},
                ]
            });
        },
        delete(id, index) {
            this.apiDeleteAutomations(id)
                .then(() => {
                    Vue.delete(this.automations, index);
                    this.$snotify.success(this.$t('Automation deleted.'));
                });
        }
    },
}
</script>
