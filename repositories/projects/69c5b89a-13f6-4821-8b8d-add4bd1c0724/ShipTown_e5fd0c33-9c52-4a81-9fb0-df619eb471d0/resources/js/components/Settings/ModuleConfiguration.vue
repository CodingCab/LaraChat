<template>
    <div>
        <search-and-option-bar-observer/>
        <search-and-option-bar :isStickable="true">
            <search-filter placeholder="Search " @search="searchModule" :searchValue="getUrlFilter('search')"
                           autofocus></search-filter>
        </search-and-option-bar>

        <div class="list-group mt-2">
            <template v-for="module in modules">
                <div class="setting-list">

                    <div class="setting-body flex-fill">
                        <div class="setting-title">{{ module.name }}</div>
                        <div class="setting-desc">{{ module.description }}</div>
                    </div>

                    <template v-if="module.settings_link">
                        <a :href="module.settings_link" :dusk="convertPathFormat(module.settings_link)"
                           class="btn-link">
                            <div class="setting-icon text-right bg-white">
                                <font-awesome-icon icon="cog" class="fa-sm bg-white"></font-awesome-icon>
                            </div>
                        </a>
                    </template>

                    <div class="custom-control custom-switch m-auto text-right align-content-center">
                        <input type="checkbox" @change="updateModule(module)" class="custom-control-input"
                               :id="module.id" v-model="module.enabled">
                        <label class="custom-control-label" :for="module.id"></label>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>

<script>
import api from "../../mixins/api";
import helpers from "../../mixins/helpers";
import url from "../../mixins/url";
import SearchFilter from "../../components/UI/SearchFilter.vue";

export default {
    mixins: [api, helpers, url],

    components: {
        SearchFilter
    },

    created() {
        this.loadModules();
    },

    data: () => ({
        error: false,
        modules: [],
    }),

    methods: {
        loadModules() {
            const params = {
                'filter[search]': this.getUrlFilter('search'),
                'sort': 'name',
            }
            this.apiGetModules(params)
                .then(({data}) => {
                    this.modules = data.data;
                    this.setFocusElementById('search-filter');
                }).catch((error) => {
                this.displayApiCallError(error);
            });
        },

        searchModule(q) {
            this.setUrlParameter('filter[search]', q);
            this.loadModules();
        },

        updateModule(module) {
            this.apiPostModule(module.id, {
                'enabled': module.enabled
            })
                .catch((error) => {
                    let errorMsg = 'Error ' + error.response.status + ': ' + JSON.stringify(error.response.data);

                    this.notifyError(errorMsg, {
                        closeOnClick: true,
                        timeout: 0,
                        buttons: [
                            {text: 'OK', action: null},
                        ]
                    });

                    this.loadModules();
                }).finally(() => {
                this.loadModules();
            });
        },

        convertPathFormat(path) {
            return path
                .replace(/^\//, '')
                .split('/')
                .map((part, index) => index === 0 ? part : part.charAt(0).toUpperCase() + part.slice(1))
                .join('');
        }
    }
}
</script>
