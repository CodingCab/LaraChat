<template>
    <div>
        <div class="card card-default">
            <div class="card-header">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span>
                        {{ $t('Warehouse') }}
                    </span>
                    <button type="button" class="action-link btn btn-sm btn-light" @click="showCreateForm()">
                        {{ $t('Add New') }}
                    </button>
                </div>
            </div>

            <div class="card-body">
                <table v-if="warehouses.length > 0" class="table table-hover table-borderless table-responsive mb-0">
                    <thead>
                        <tr>
                            <th>{{ $t('Code') }}</th>
                            <th>{{ $t('Name') }}</th>
                            <th>{{ $t('Tags') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="warehouse in warehouses" :key="warehouse.id" @click.prevent="showEditForm(warehouse)">
                            <td>{{ warehouse.code }}</td>
                            <td>{{ warehouse.name }}</td>
                            <td>
                                <a v-for="tag in warehouse.tags" class="badge text-uppercase" :key="tag.id">
                                    {{ tag.name }}
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <p v-else class="mb-0">
                    {{ $t('No warehouses found.') }}
                </p>
            </div>
        </div>
        <!-- The modals -->
        <create-modal id="createForm" @onCreated="addWarehouse"></create-modal>
        <edit-modal :warehouse="selectedWarehouse" id="editForm" @onUpdated="warehouseUpdatedEvent"></edit-modal>
        <find-address-modal id="findAddressForm"></find-address-modal>
        <new-address-modal />
    </div>
</template>

<script>

import CreateModal from './Warehouse/CreateModal';
import EditModal from './Warehouse/EditModal';
import api from "../../mixins/api.vue";
import Modals from "../../plugins/Modals";

export default {
    mixins: [api],
    components: {
        'create-modal': CreateModal,
        'edit-modal': EditModal
    },

    mounted() {
        this.fetchWarehouses();

        Modals.EventBus.$on('hide::modal::find-address-modal', (data) => {
            if (typeof data.saveChanges !== 'undefined' && data.saveChanges && data.warehouseAddress) {
                this.selectedWarehouseAddress = data.warehouseAddress;
                this.updateWarehouseAddress();
            }
        });
    },

    data: () => ({
        warehouses: [],
        selectedWarehouse: {},
        selectedWarehouseAddress: null,
    }),

    methods: {
        fetchWarehouses: function () {
            this.apiGetWarehouses({
                'per_page': 100,
                'sort': 'code',
                'include': 'tags,address'
            })
                .then(({ data }) => {
                    this.warehouses = data.data;
                })
        },

        showCreateForm() {
            $('#createForm').modal('show');
        },

        showEditForm(warehouse) {
            this.selectedWarehouse = warehouse;
            $('#editForm').modal('show');
        },

        addWarehouse(orderStatus) {
            this.warehouses.push(orderStatus)
        },

        warehouseUpdatedEvent(newValue) {
            this.fetchWarehouses();
        },

        updateWarehouseAddress() {
            this.apiPutWarehouses(this.selectedWarehouse.id, {
                address_id: this.selectedWarehouseAddress,
                tags: this.selectedWarehouse.tags.map(tag => tag.name)
            }).then(({ data }) => {
                this.fetchWarehouses();
                this.$snotify.success(this.$t('Warehouse address updated successfully'));
            });
        },
    },
}
</script>
