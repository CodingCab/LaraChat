<template>
    <div>
        <users-list :roles="roles"></users-list>
        <users-roles :roles="roles" @refreshRoles="refreshRoles"></users-roles>
    </div>
</template>

<script>
import List from './Users/List';
import Roles from './Users/Roles';
import api from "../mixins/api";

export default {
    mixins: [api],
    components: {
        'users-list': List,
        'users-roles': Roles,
    },

    data: () => ({
        roles: []
    }),

    mounted() {
        this.loadRoles();
    },

    methods: {
        loadRoles() {
            this.apiGetUserRoles()
                .then(({ data }) => {
                    this.roles = data.data;
                })
                .catch(e => {
                    this.displayApiCallError(e);
                });
        },

        refreshRoles() {
            this.loadRoles();
        }
    },
}
</script>
