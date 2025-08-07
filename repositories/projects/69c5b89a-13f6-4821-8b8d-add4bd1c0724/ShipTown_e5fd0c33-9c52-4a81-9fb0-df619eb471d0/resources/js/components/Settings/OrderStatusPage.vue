<template>
    <div>
        <report ref="report" report-url="/api/orders-statuses" @click="showEditForm">
            <template #report-header>
                {{ $t('Order Statuses') }}
            </template>
            <template #buttons>
                <top-nav-button icon="plus" buttonId="add-button" @click="showCreateForm"/>
            </template>
        </report>
        <create-modal id="createForm" @onCreated="reloadReport"></create-modal>
        <edit-modal :orderStatus="selectedOrderStatus" id="editForm" @onUpdated="reloadReport"/>
    </div>
</template>

<script>
import CreateModal from './OrderStatus/CreateModal';
import EditModal from './OrderStatus/EditModal';
import api from "../../mixins/api.vue";
import url from "../../mixins/url";

export default {
    name: 'OrderStatusPage',
    mixins: [api, url],
    components: {
        CreateModal,
        EditModal,
    },
    data() {
        return {
            selectedOrderStatus: {}
        }
    },
    methods: {
        showCreateForm() {
            $('#createForm').modal('show');
        },
        showEditForm(record) {
            this.selectedOrderStatus = record;
            $('#editForm').modal('show');
        },
        reloadReport() {
            this.setUrlParameter('t', new Date().getTime(), true);
        }
    }
}
</script>
