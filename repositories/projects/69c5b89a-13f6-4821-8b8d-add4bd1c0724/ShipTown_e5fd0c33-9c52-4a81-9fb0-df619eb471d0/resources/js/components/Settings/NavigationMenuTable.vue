<template>
    <div>
        <div class="card card-default">
            <div class="card-header">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span>
                        {{ $t('Navigation Menu') }}
                    </span>
                    <button type="button" class="action-link btn btn-sm btn-light" @click="showCreateForm()">
                        {{ $t('Add New') }}
                    </button>
                </div>
            </div>

            <div class="card-body">
                <table v-if="navigations.length > 0" class="table table-borderless table-responsive mb-0">
                    <thead>
                        <tr>
                            <th>{{ $t('Group') }}</th>
                            <th>{{ $t('Name') }}</th>
<!--                            <th>ID</th>-->
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(navigationMenu, i) in navigations" :key="i">
                            <td>{{ navigationMenu.group }}</td>
                            <td>{{ navigationMenu.name }}</td>
<!--                            <td>{{ navigationMenu.id }}</td>-->
                            <td>
                                <a @click.prevent="showEditForm(navigationMenu)">
                                    <font-awesome-icon icon="edit"></font-awesome-icon>
                                </a>
                                <a @click.prevent="confirmDelete(navigationMenu)">
                                    <font-awesome-icon icon="trash"></font-awesome-icon>
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <p v-else class="mb-0">
                    {{ $t('No navigation menu found.') }}
                </p>
            </div>
        </div>
        <!-- The modals -->
        <create-modal id="createForm" @onCreated="addNavigationMenu"></create-modal>
        <edit-modal :navigationMenu="selectedNavigationMenu" id="editForm" @onUpdated="updateNavigationMenu"></edit-modal>
    </div>
</template>

<script>

import CreateModal from './NavigationMenu/CreateModal';
import EditModal from './NavigationMenu/EditModal';
import api from "../../mixins/api.vue";

export default {
    mixins: [api],
    components: {
        'create-modal': CreateModal,
        'edit-modal': EditModal,
    },

    mounted() {
        this.apiGetNavigationMenu({
                'sort': 'group,name',
                'per_page': 999
            })
            .then(({ data }) => {
                this.navigations = data.data;
            })
    },

    data: () => ({
        navigations: [],
        selectedNavigationMenu: {}
    }),

    methods: {
        showCreateForm(){
            $('#createForm').modal('show');
        },
        showEditForm(navigationMenu) {
            this.selectedNavigationMenu = navigationMenu;
            $('#editForm').modal('show');
        },
        addNavigationMenu(orderStatus){
            this.navigations.push(orderStatus)
        },
        updateNavigationMenu(newValue) {
            const indexNavigationMenu = this.navigations.findIndex(navigationMenu => navigationMenu.id === newValue.id)
            this.$set(this.navigations, indexNavigationMenu, newValue)
        },
        confirmDelete(navigationMenu) {
            const indexNavigationMenu = this.navigations.findIndex(menu => navigationMenu.id === menu.id)
            this.$snotify.confirm(this.$t('Once deleted, data cannot be restored'), this.$t('Are you sure?'), {
                position: 'centerCenter',
                buttons: [
                    {
                        text: this.$t('Yes'),
                        action: (toast) => {
                            this.delete(navigationMenu.id, indexNavigationMenu)
                            this.$snotify.remove(toast.id);
                        }
                    },
                    {text: this.$t('Cancel')},
                ]
            });
        },
        delete(id, index) {
            this.apiDeleteNavigationMenu(id)
                .then(() => {
                    Vue.delete(this.navigations, index);
                    this.$snotify.success(this.$t('Navigation menu deleted.'));
                });
        }
    },
}
</script>
