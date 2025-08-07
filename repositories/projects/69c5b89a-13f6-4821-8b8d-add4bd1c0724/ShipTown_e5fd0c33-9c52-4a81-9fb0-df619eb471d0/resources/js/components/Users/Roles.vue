<template>
    <div>
        <div class="card card-default">
            <div class="card-header">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span>
                        {{ $t('Roles') }}
                    </span>
                    <a tabindex="-1" class="action-link mr-2" v-b-modal.create-role-modal>
                        {{ $t('Add Role') }}
                    </a>
                </div>
            </div>
            <div class="card-body">
                <table v-if="roles.length > 0" class="table table-borderless table-responsive mb-0">
                    <tbody>
                        <tr v-for="(role, i) in roles" :key="'role-' + i">
                            <td>{{ role.name.charAt(0).toUpperCase() + role.name.slice(1) }}</td>
                            <td v-if="role.name !== 'admin' && role.name !== 'user'">
                                <a @click="onDeleteClick(role.id)" class="delete-btn">
                                    <font-awesome-icon icon="trash"></font-awesome-icon>
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <p v-else class="mb-0">
                    {{ $t('No role found.') }}
                </p>
            </div>
        </div>
        <b-modal ref="createRoleModal" id="create-role-modal" :title="$t('Create Role')" @ok="handleAddOk" no-fade>
            <create-role-modal ref="createRoleForm" @onCreated=addedRole>
            </create-role-modal>
            <template #modal-footer="{ ok, cancel }">
                <b-button variant="secondary" @click="cancel()">
                    {{ $t('Cancel') }}
                </b-button>
                <b-button variant="primary" @click="ok()">
                    {{ $t('Save') }}
                </b-button>
            </template>
        </b-modal>
    </div>
</template>

<script>
import api from "../../mixins/api";
import Loading from "../../mixins/loading-overlay";
import CreateRole from "./CreateRole";

export default {
    mixins: [api, Loading],

    components: {
        'create-role-modal': CreateRole,
    },

    props: {
        roles: {
            type: Array,
            required: true,
        }
    },

    methods: {
        onDeleteClick(id) {
            this.apiDeleteUserRole(id).then(() => {
                this.$emit('refreshRoles');
                this.$snotify.success(this.$t('Role deleted'));
            });
        },

        addedRole() {
            this.$emit('refreshRoles');
            this.$snotify.success(this.$t('Role created'));
            this.$refs.createRoleModal.hide();
        },

        handleAddOk(bvModalEvt) {
            bvModalEvt.preventDefault();
            this.$refs.createRoleForm.submit();
        },
    }
}
</script>

<style scoped lang="scss">
.delete-btn {
    cursor: pointer;
    transition: color 0.3s ease;

    &:hover {
        color: red;
    }
}
</style>
