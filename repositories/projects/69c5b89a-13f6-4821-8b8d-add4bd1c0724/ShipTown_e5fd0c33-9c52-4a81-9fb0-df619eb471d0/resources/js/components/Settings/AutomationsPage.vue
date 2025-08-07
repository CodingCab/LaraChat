<template>
    <div>
        <report ref="report" report-url="/api/modules/automations" @click="showEditForm">
            <template #report-header>
                {{ $t('Active Orders Automations') }}
            </template>
            <template #buttons>
                <top-nav-button icon="plus" buttonId="add-button" dusk="add-new-button" @click="showCreateForm"/>
            </template>
        </report>
        <create-modal id="createForm" :events="events" @onCreated="reloadReport"></create-modal>
        <edit-modal :selectedAutomation="selectedAutomation" :events="events" id="editForm" @onUpdated="reloadReport" @onCopied="reloadReport"></edit-modal>
    </div>
</template>

<script>
import CreateModal from './Automations/CreateModal';
import EditModal from './Automations/EditModal';
import api from "../../mixins/api.vue";
import url from "../../mixins/url";

export default {
    name: 'AutomationsPage',
    mixins: [api, url],
    components: {
        CreateModal,
        EditModal,
    },
    data() {
        return {
            selectedAutomation: {},
            events: []
        }
    },
    mounted() {
        this.apiGetAutomationConfig()
            .then(({data}) => {
                if (Array.isArray(data.events)) {
                    this.events = data.events;
                } else if (Array.isArray(data)) {
                    this.events = data;
                } else {
                    this.events = [data];
                }
            });
    },
    methods: {
        showCreateForm() {
            $('#createForm').modal('show');
        },
        showEditForm(record) {
            this.selectedAutomation = record;
            $('#editForm').modal('show');
        },
        reloadReport() {
            this.setUrlParameter('t', new Date().getTime(), true);
        }
    }
}
</script>
