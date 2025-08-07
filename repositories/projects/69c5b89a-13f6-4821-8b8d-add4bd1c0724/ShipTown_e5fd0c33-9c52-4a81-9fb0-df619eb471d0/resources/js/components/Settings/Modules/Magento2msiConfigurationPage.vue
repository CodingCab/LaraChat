<template>
    <div>
        <div class="card card-default">
            <div class="card-header">
                <div style="display: flex; justify-content: space-between; align-items: center;" class="btn" @click="show['create_new_connection_tab'] = ! show['create_new_connection_tab']">
                    <span>
                        {{ $t('New Connection') }}
                    </span>
                    <span class="btn-outline-light text-dark font-weight-bold small">
                        <span v-if="! show['create_new_connection_tab']"><</span>
                    </span>
                </div>
            </div>

            <div class="card-body" v-if="show['create_new_connection_tab']">
                <form class="form" @submit.prevent="submit" ref="loadingContainer">
                    <div class="form-group">
                        <label class="form-label" for="base_url">{{ $t('Base URL') }}</label>
                        <input v-model="newConnection.base_url" class="form-control" id="create-base_url" type="url" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="api_access_token">{{ $t('Access Token') }}</label>
                        <input v-model="newConnection.api_access_token" class="form-control" id="api_access_token" type="password" required>
                    </div>

                    <div class="form-group">
                        <button class="btn btn-primary fa-pull-right" @click.prevent="submit">{{ $t('Connect') }}</button>
                    </div>
                </form>
            </div>
        </div>

        <a class="btn btn-block btn-primary" :href="'/settings/modules/magento2api/inventory-source-items'">{{ $t('inventory-source-items') }}</a>

        <br>
        <table style="width: 100%" class="table table-fit table-hover table-borderless table-responsive mb-0 rounded">
            <thead>
                <tr class="small table-active w-auto w-100">
                    <th class="w-100 w-auto">URL</th>
                    <th style="min-width: 200px !important;">{{ $t('INVENTORY SOURCE') }}</th>
                    <th style="min-width: 200px !important;">{{ $t('WAREHOUSE TAG') }}</th>
                </tr>
            </thead>
            <tbody>
                <template v-for="connection in connections">
                    <tr class="w-100 w-auto">
                        <td class="w-auto text-nowrap" colspan="3">{{ connection.base_url }}</td>
                    </tr>
                    <tr>
                        <td class="w-auto w-100">&nbsp;</td>
                        <td class="fa-pull-right">
                            <select class="form-control" v-model="connection['magento_source_code']" @change="updateConnection(connection)">
                                <option :value="null">--- {{ $t('DO NOT SYNC') }} ---</option>
                                <template v-for="inventory_source in connection['inventory_sources']">
                                    <option :value="inventory_source['source_code']">{{ inventory_source['source_code'] }}</option>
                                </template>
                            </select>
                        </td>
                        <td>
                            <select class="form-control" v-model="connection['inventory_source_warehouse_tag_id']" @change="updateConnection(connection)">
                                <option :value="null">--- {{ $t('DO NOT SYNC') }} ---</option>
                                <template v-for="warehouse_tag in warehouse_tags">
                                    <option :value="warehouse_tag['tag_id']">{{ warehouse_tag['tag_name'].toUpperCase() }}</option>
                                </template>
                            </select>
                        </td>
                    </tr>
                </template>

                <tr v-if="(!connections) || connections.length === 0">
                    <td colspan="2">
                        {{ $t('You have not created any Magento Api connections.') }}
                    </td>
                </tr>

            </tbody>
        </table>
    </div>
</template>

<script>

import CreateModal from "../Automations/CreateModal";
import EditModal from "../Automations/EditModal";
import api from "../../../mixins/api";
import * as url from "url";

export default {
    computed: {
        url() {
            return url
        }
    },
    components: {
        CreateModal, EditModal
    },

    mixins: [api],

    data: () => ({
        newConnection: {
            base_url: '',
            api_access_token: '',
        },
        show: {
            create_new_connection_tab: false,
        },
        selectedConnection: null,
        connections: [],
        warehouse_tags: [],
    }),

    mounted() {
        this.reloadConnections();
        this.reloadWarehouseTags();
    },

    methods: {
        updateConnection(connection) {
            this.apiPutMagento2msiConnection(connection.id, {
                'magento_source_code': connection.magento_source_code,
                'inventory_source_warehouse_tag_id': connection.inventory_source_warehouse_tag_id
            })
        },

        reloadWarehouseTags: function () {
            this.apiGetProductTags({
                'filter[taggable_type]': 'App\\Models\\Warehouse',
                'include': 'tag',
                'per_page': 999
            })
            .then(({data}) => {
                this.warehouse_tags = data.data.map((tag) => {
                    return {
                        'tag_name': tag.tag.name.en,
                        'tag_id': tag.tag.id
                    }
                });
            })
            .catch((error) => {
                this.displayApiCallError(error);
            });
        },

        reloadConnections() {
            this.apiGetMagento2msiConnections({
                    'per_page': 100,
                    'include': 'tags,warehouse'
                })
                .then(({ data }) => {
                    this.connections = data.data;
                })
                .catch((error) => {
                    this.displayApiCallError(error);
                });
        },

        confirmDelete(connection_id){
            this.$snotify.confirm('After delete data cannot restored', 'Are you sure?', {
                position: 'centerCenter',
                buttons: [
                    {
                        text: 'Yes',
                        action: (toast) => {
                            this.handleDelete(connection_id)
                            this.$snotify.remove(toast.id);
                        }
                    },
                    {text: 'Cancel'},
                ]
            });
        },

        submit() {
            this.apiPostMagento2msiConnection({...this.newConnection})
                .then(() => {
                    this.newConnection = {
                        base_url: '',
                        api_access_token: '',
                    };
                    this.show.create_new_connection_tab = false;
                })
                .catch((error) => {
                    this.displayApiCallError(error);
                })
                .finally(() => {
                    this.reloadConnections();
                });
        },
    },
}

</script>

<style lang="scss" scoped>
.action-link {
    cursor: pointer;
}
</style>
