<template>
    <div>
        <search-and-option-bar-observer/>
        <search-and-option-bar :isStickable="true">
            <barcode-input-field :input_id="'barcode_input'" :placeholder="$t('search')" ref="barcode"
                                 :url_param_name="'search'"
                                 @refreshRequest="reloadPermissions" @barcodeScanned="reloadPermissions"
                                 :show-barcode-scanner-button="false"/>
        </search-and-option-bar>

        <div class="row pl-2 p-0">
            <breadcrumbs></breadcrumbs>
        </div>

        <template
            v-if="isLoading === false && permissions !== null && Array.isArray(permissions) && permissions.length === 0">
            <div class="text-secondary text-center mt-3">
                {{ $t('No Records Found') }}
            </div>
        </template>

        <template v-else>
            <div class="row mt-3">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            {{ $t('permissions') }}
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th class="text-nowrap text-center">{{ $t('permission') }}</th>
                                    <th v-for="role in roles" :key="role.id" class="text-center"
                                        style="vertical-align: middle;">
                                        {{ role.name.charAt(0).toUpperCase() + role.name.slice(1) }}
                                        <div v-if="role.name !== 'admin'">
                                            <input type="checkbox" @input="(e) => selectAllPermissions(e, role)">
                                        </div>
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="(permission, key) in permissions" :key="key">
                                    <td>{{ permission.name }}</td>
                                    <td v-for="role in roles" :key="role.id" class="text-center">
                                        <input type="checkbox" :id="`${permission.name}-${role.id}`"
                                               :data-role="role.name"
                                               :name="`${permission.name}-${role.id}`" :value="permission.id"
                                               v-model="grantedPermissions[role.id]" @change="updatePermissions"
                                               :disabled="role.name === 'admin'">
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <div class="row">
            <div class="col">
                <div ref="loadingContainerOverride" style="height: 32px"></div>
            </div>
        </div>
    </div>
</template>

<script>
import loadingOverlay from '../../../mixins/loading-overlay';
import api from '../../../mixins/api';
import url from '../../../mixins/url';
import Breadcrumbs from '../../Reports/Breadcrumbs.vue';
import BarcodeInputField from '../../SharedComponents/BarcodeInputField';
import {throttle} from 'lodash';

export default {
    mixins: [loadingOverlay, api, url],

    components: {
        Breadcrumbs,
        BarcodeInputField
    },

    data() {
        return {
            pagesLoadedCount: 1,
            reachedEnd: false,
            scrollPercentage: 70,
            perPage: 20,
            permissions: [],
            grantedPermissions: {},
            roles: [],
            permissionsChanged: false
        };
    },

    mounted() {
        window.onscroll = throttle(() => this.loadMore(), 100);

        this.fetchGrantedPermissions();
        this.fetchPermissions();
        this.fetchRoles();
    },

    methods: {
        reloadPermissions() {
            this.permissions = null;
            this.fetchPermissions();
        },

        fetchPermissions(page = 1) {
            this.showLoading();

            const params = {...this.$router.currentRoute.query};
            params['filter[search]'] = this.getUrlParameter('search');
            params['include'] = 'roles';
            params['per_page'] = this.perPage;
            params['page'] = page;

            this.apiGetPermissions(params)
                .then(({data}) => {
                    this.permissions = this.permissions ? this.permissions.concat(data.data) : data.data
                    this.reachedEnd = data.data.length === 0;
                    this.pagesLoadedCount = page;

                    this.scrollPercentage = (1 - this.perPage / this.permissions.length) * 100;
                    this.scrollPercentage = Math.max(this.scrollPercentage, 70);
                })
                .catch((error) => {
                    this.displayApiCallError(error);
                })
                .finally(() => {
                    this.hideLoading();
                });
        },

        fetchRoles() {
            this.apiGetUserRoles()
                .then(({data}) => {
                    this.roles = data.data;
                });
        },

        fetchGrantedPermissions() {
            const params = {...this.$router.currentRoute.query};
            params['include'] = 'roles';
            params['per_page'] = 9999;
            params['page'] = 1;

            this.apiGetPermissions(params)
                .then(({data}) => {
                    this.setGrantedPermissions(data.data);
                });
        },

        updatePermissions() {
            this.apiUpdatePermissions({permissions: JSON.stringify(this.grantedPermissions)})
                .then(() => {
                    this.fetchGrantedPermissions();
                    this.notifySuccess(this.$t('Permissions updated successfully'));
                }).catch((error) => {
                this.displayApiCallError(error);
            });
        },

        selectAllPermissions(e, role) {
            const selectedPermissions = this.permissions.map(permission => permission.id);
            const allPermissions = this.grantedPermissions[role.id];

            if (e.target.checked) {
                const mergedPermissions = [...selectedPermissions, ...allPermissions];
                this.grantedPermissions[role.id] = [...new Set(mergedPermissions)];
            } else {
                this.grantedPermissions[role.id] = allPermissions.filter(permission => !selectedPermissions.includes(permission));
            }

            this.updatePermissions();
        },

        loadMore() {
            if (this.isMoreThanPercentageScrolled(this.scrollPercentage) && this.hasMorePagesToLoad() && !this.isLoading) {
                this.fetchPermissions(++this.pagesLoadedCount);
            }
        },

        hasMorePagesToLoad() {
            return this.reachedEnd === false;
        },

        setGrantedPermissions(permissions) {
            let temp = {};

            permissions.forEach(permission => {
                permission.roles.forEach(role => {
                    if (!temp[role.id]) {
                        temp[role.id] = [];
                    }

                    temp[role.id].push(permission.id);
                });
            });

            this.grantedPermissions = temp;
        }
    },
};
</script>
