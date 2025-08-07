<template>
    <div>
        <report ref="report" report-url="/api/navigation-menu" @click="showEditForm">
            <template #report-header>
                {{ $t('Navigation Menu') }}
            </template>
            <template #buttons>
                <top-nav-button icon="plus" buttonId="add-button" @click="showCreateForm"/>
            </template>
        </report>
        <create-modal id="createForm" @onCreated="reloadReport"></create-modal>
        <edit-modal :navigationMenu="selectedNavigationMenu" id="editForm" @onUpdated="reloadReport" @onDeleted="reloadReport"></edit-modal>
    </div>
</template>

<script>
import CreateModal from './NavigationMenu/CreateModal';
import EditModal from './NavigationMenu/EditModal';
import api from "../../mixins/api.vue";
import url from "../../mixins/url";

export default {
    name: 'NavigationMenuPage',
    mixins: [api, url],
    components: {
        CreateModal,
        EditModal,
    },
    data() {
        return {
            selectedNavigationMenu: {}
        }
    },
    methods: {
        showCreateForm() {
            $('#createForm').modal('show');
        },
        showEditForm(record) {
            this.selectedNavigationMenu = record;
            $('#editForm').modal('show');
        },
        reloadReport() {
            this.setUrlParameter('t', new Date().getTime(), true);
        }
    }
}
</script>
