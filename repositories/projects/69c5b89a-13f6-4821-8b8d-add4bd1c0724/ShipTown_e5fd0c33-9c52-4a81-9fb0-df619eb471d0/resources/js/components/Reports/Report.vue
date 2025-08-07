<template>
    <container>
        <template v-if="!showOnlyDataTable">
            <search-and-option-bar-observer class="d-print-none"/>
            <search-and-option-bar :isStickable="true" class="d-print-none">
                <barcode-input-field :input_id="'barcode_input'" url_param_name="filter[search_contains]" :placeholder="$t('Search')"/>
                <template v-slot:buttons>
                    <slot name="buttons"></slot>
                    <top-nav-button icon="sliders" buttonId="columns-button" @click="showShowHideColumnsModal"/>
                    <top-nav-button icon="ellipsis-vertical" id="options-button" v-b-modal="'optionsModal'"/>
                </template>
            </search-and-option-bar>
        </template>

        <report-head v-if="!showOnlyDataTable" :report-name="breadcrumbs" :auto-expand-filters="(records === null || records.length === 0)" @editFilter="editFilter"></report-head>

        <slot>
            <template v-if="records && records.length > 0">
                    <div v-if="showOnlyDataTable">
                        <report-table :columns="visibleColumns" :records="records" @click="emitClickEvent" @showFilterModal="showFilterModal"></report-table>
                    </div>
                    <card v-else class="align-text-top align-top h-100 flex-fill">
                        <report-table :columns="visibleColumns" :records="records" @click="emitClickEvent" @showFilterModal="showFilterModal"></report-table>
                        <div v-if="!hasMoreRecords" class="text-secondary small text-center mt-3">{{ $t('No more records found.') }}</div>
                        <div v-if="isLoading" ref="loadingContainerOverride" style="height: 32px"></div>
                    </card>
            </template>
        </slot>

        <div v-if="!isLoading && (records === null || records.length === 0)" class="text-secondary small text-center pt-2">
            {{ $t('No records found with filters specified') }}
        </div>

        <div v-if="isLoading && (records === null || records.length === 0)" ref="loadingContainerOverride" style="height: 32px"></div>

        <b-modal id="filter-box-modal" size="sm" no-fade hide-header @shown="focusFilterBoxInput">
            <div v-if="filterAdding" class="d-flex flex-column" style="gap: 5px;">
                <div class="text-center font-weight-bold">{{ $t(filterAdding.selectedField.display_name) }}</div>
                <select v-model="filterAdding.selectedOperator" @change="focusFilterBoxInput" class="form-control form-control-sm">
                    <option v-for="operator in filterAdding.operators" :key="operator" :value="operator">
                        {{
                            operator === 'btwn'
                                ? $t('between')
                                : operator === 'not starts with'
                                    ? $t('does not starts with')
                                    : $t(operator)
                        }}
                    </option>
                </select>
                <form @submit.prevent="addFilter" class="d-flex flex-row" style="grid-gap: 5px;">
                    <!-- between filter inputs -->
                    <template v-if="filterAdding.selectedOperator === 'btwn'">
                        <input @keydown.enter="addFilter" v-model="filterAdding.value" id='inputFilterBetweenValueFrom' :type="['numeric','integer'].includes(filterAdding.selectedField.type) ? 'number' : 'text'" class="form-control form-control-sm">
                        <input @keydown.enter="addFilter" v-model="filterAdding.valueBetween" id='inputFilterBetweenValueTo' :type="['numeric','integer'].includes(filterAdding.selectedField.type) ? 'number' : 'text'" class="form-control form-control-sm">
                    </template>

                    <!-- other filters -->
                    <template v-else>
                      <input @keydown.enter="addFilter" v-model="filterAdding.value" id='inputFilterValue' :type="['numeric','integer'].includes(filterAdding.selectedField.type) ? 'number' : 'text'" class="form-control form-control-sm">
                    </template>
                </form>
            </div>
            <template #modal-footer>
                <b-button variant="secondary" class="float-right" @click="$bvModal.hide('filter-box-modal')">{{ $t('Cancel') }}</b-button>
                <b-button variant="primary" class="float-right" @click="addFilter">{{ $t('Apply') }}</b-button>
            </template>
        </b-modal>

        <ModalDateBetweenSelector @apply="addFilter"/>

        <options-modal>
            <modal-button @click="downloadFile">{{ $t('Download CSV') }}</modal-button>
            <modal-button id="copy-as-table-button" @click="copyAsTable">{{ $t('Copy as table') }}</modal-button>
            <modal-button @click="showMemorizeReportModal">{{ $t('Memorize Report') }}</modal-button>
            <modal-button id="schedule-report-button" @click="showScheduledReportCreateModal">{{ $t('Schedule Report') }}</modal-button>
            <br v-if="meta['actions'] && meta['actions'].length > 0"/>
            <template v-for="customAction in meta['actions']">
                <modal-button @click="clickCustomAction(customAction)">{{ $t(customAction['name']) }}</modal-button>
            </template>
        </options-modal>

        <b-modal title="Memorize Report" id="memorize-report-modal" no-fade @hidden="setFocusElementById('barcode_input')">
            <ValidationObserver ref="form">
                <form class="form" ref="loadingContainer">
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label" for="name">{{ $t('Name') }}</label>
                        <div class="col-sm-9">
                            <ValidationProvider vid="name" name="name" v-slot="{ errors }">
                                <input v-model="memorizeName" :class="{
                                    'form-control': true,
                                    'is-invalid': errors.length > 0,
                                }" id="memorize-name" required>
                                <div class="invalid-feedback">{{ errors[0] }}</div>
                            </ValidationProvider>
                        </div>
                    </div>
                </form>
            </ValidationObserver>
            <template #modal-footer>
                <b-button variant="secondary" class="float-right" @click="$bvModal.hide('memorize-report-modal');">
                    {{ $t('Cancel') }}
                </b-button>
                <b-button variant="primary" class="float-right" @click="memorizeReport">
                    {{ $t('OK') }}
                </b-button>
            </template>
        </b-modal>

        <b-modal id="show-hide-columns-local-modal" no-fade header-class="small" @hidden="setFocusElementById('barcode_input')">
            <template #modal-header>{{ $t('Show \ Hide Columns') }}</template>
            <b-form-group>
                <draggable v-model="columns" handle=".drag-handle" @start="drag=true" @end="drag=false">
                    <div class="d-flex" v-for="field in columns" :key="field.name">
                        <div class="drag-handle mr-2 mt-2" style="cursor: move;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M3 9.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3z"/>
                            </svg>
                        </div>
                        <b-form-checkbox v-if="field" v-model="field.visible" :key="field.name" class="mt-2"></b-form-checkbox>
                        <b-dropdown :text="field.display_name" :id="'show-hide-columns-' + field.name" toggle-class="minimal-dropdown-toggle small text-secondary" variant="link" class="dropdown-text d-flex small text-secondary">
                            <b-dropdown-item @click="setUrlParameterAngGo('sort',['-', field.name].join(''))">
                                <icon-sort-desc class="mr-1" /> {{ $t('Sort Descending') }}
                            </b-dropdown-item>
                            <b-dropdown-item @click="setUrlParameterAngGo('sort', field.name)" >
                                <icon-sort-asc class="mr-1" /> {{ $t('Sort Ascending') }}
                            </b-dropdown-item>
                            <b-dropdown-item :id="'show-hide-columns-filter-by-value-' + field.name" @click="showFilterModal(field)">
                                <icon-filter class="mr-1" /> {{ $t('Filter by value') }}
                            </b-dropdown-item>
                        </b-dropdown>
                    </div>
                </draggable>
            </b-form-group>

            <template #modal-footer>
                <b-button variant="secondary" class="float-right" @click="$bvModal.hide('show-hide-columns-local-modal');">{{ $t('Cancel') }}</b-button>
                <b-button variant="primary" class="float-right" @click="updateVisibleFieldsAndGo">{{ $t('OK') }}</b-button>
            </template>
        </b-modal>

        <scheduled-report-create-modal></scheduled-report-create-modal>
    </container>
</template>

<script>
import loadingOverlay from '../../mixins/loading-overlay';
import url from "../../mixins/url";
import api from "../../mixins/api";
import IconSortAsc from "../UI/Icons/IconSortAsc.vue";
import IconSortDesc from "../UI/Icons/IconSortDesc.vue";
import IconFilter from "../UI/Icons/IconFilter.vue";
import ModalDateBetweenSelector from "../Widgets/ModalDateBetweenSelector.vue";
import ReportHead from "./ReportHead.vue";
import moment from "moment";
import helpers from "../../helpers";
import ProductSkuButton from "../SharedComponents/ProductSkuButton.vue";
import ScheduledReportCreateModal from '../ScheduledReport/ScheduledReportCreateModal.vue';

import {ValidationObserver, ValidationProvider} from "vee-validate";
import ModalButton from "./ModalButton.vue";
import ReportTable from "./ReportTable.vue";
import axios from "axios";
import OptionsModal from "../OptionsModal.vue";
import draggable from 'vuedraggable';

export default {
        mixins: [loadingOverlay, url, api, helpers],

        components: {
            OptionsModal,
            ReportTable,
            ModalButton,
            ProductSkuButton,
            IconSortAsc,
            IconSortDesc,
            IconFilter,
            ModalDateBetweenSelector,
            ReportHead,
            ValidationProvider,
            ValidationObserver,
            ScheduledReportCreateModal,
            draggable
        },

        props: {
            autoload: {
                type: Boolean,
                default: true,
            },
            params: {
                type: Object,
                default: () => {
                    return {}
                }
            },
            reportUrl: {
                type: String,
                default: null,
            },
            showOnlyDataTable: {
                type: Boolean,
                default: false,
            },
            downloadButtonText: String,
        },

        watch: {
            '$route'(to, from) {
                if (this.autoload === false) {
                    return;
                }

                if (to.path !== from.path) {
                    return;
                }

                this.$nextTick(() => {
                    this.buildFiltersFromUrl();
                    this.reloadRecords();
                });
            },
        },

        data() {
            return {
                lastResponse: null,
                records: [],
                meta: [],

                columns: [],

                filters: [],
                selected: [], // Must be an array reference!
                fields: [],
                visibleFields: [],
                visibleColumns: [],

                filterAdding: null,
                showFilters: true,
                perPage: 50,
                page: 1,
                hasMoreRecords: true,

                memorizeName: '',

                showDateFilter: true,
                drag: false,
            }
        },

        beforeMount() {
            this.setFilterAdding()
        },

        mounted() {
            this.perPage = this.getUrlParameter('per_page', 50);

            this.setFocusElementById('barcode_input');

            this.setDefaultFilters();

            this.buildFiltersFromUrl();

            if (this.autoload === true) {
                this.loadRecords();
            }

            window.onscroll = () => this.loadMoreRecords();
        },

        methods: {
            emitClickEvent(record, fieldName) {
                this.$emit('click', record, fieldName);
            },

            clickCustomAction(action) {
                const currentParams = window.location.search;
                window.location.href = action['url'] + currentParams;
            },

            setDefaultFilters() {
                const currentPath = this.$router.currentRoute.path;
                
                // Purchase Orders report default filters
                if (currentPath === '/reports/purchase-orders') {
                    // Default columns/fields to show if no select parameter is present
                    if (!this.getUrlParameter('select')) {
                        this.setUrlParameter('select', 'purchase_order,sku,name,quantity');
                    }
                }
                
                // Inventory/Products Totals report default filters
                if (currentPath === '/reports/products-inventory') {
                    // Default columns/fields to show if no select parameter is present
                    if (!this.getUrlParameter('select')) {
                        this.setUrlParameter('select', 'sku,name,quantity');
                    }
                }
            },

            updateVisibleFieldsAndGo() {
                this.$bvModal.hide('show-hide-columns-local-modal');

                this.selected = this.columns
                    .filter(column => column.visible)
                    .map(f => f.name);

                // Store the order of all columns (visible and hidden)
                const columnOrder = this.columns.map(f => f.name);

                // ensure its not sorted by invisible fields
                let sort = this.getUrlParameter('sort', '');

                if (sort && this.selected.length > 0) {
                    let sortField = sort.replace('-', '');
                    if (!this.selected.includes(sortField)) {
                        this.setUrlParameter('sort', '');
                    }
                }

                this.setUrlParameter('select', this.selected.join(','));
                this.setUrlParameter('order', columnOrder.join(','));
            },

            updateParameterAndReload(key, value) {
                this.setUrlParameter(key, value);

                this.page = 1;
                this.loadRecords();
            },

            downloadFile() {
                let filename  = this.meta['report_name']+'.csv';
                let url       = this.$router.currentRoute;
                let urlParams = new URLSearchParams(window.location.search);

                urlParams.set('filename', filename);

                helpers.downloadFile(url.path + '?' + urlParams.toString(), filename);
            },

            copyAsTable() {
                const table = this.$el.querySelector('table');
                if (!table) {
                    return;
                }

                let text = '';
                table.querySelectorAll('tr').forEach(row => {
                    const rowText = [];
                    row.querySelectorAll('th,td').forEach(cell => {
                        if (cell.dataset.value !== undefined && cell.innerText.trim() === '-') {
                            rowText.push(cell.dataset.value);
                        } else {
                            rowText.push(cell.innerText.trim());
                        }
                    });
                    text += rowText.join('\t') + '\n';
                });

                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(text).then(() => {
                        this.notifySuccess('Table copied');
                    }).catch(() => {
                        this.notifyError('Failed to copy table');
                    });
                } else {
                    // Fallback for browsers that do not support navigator.clipboard
                    const textarea = document.createElement('textarea');
                    textarea.value = text;
                    document.body.appendChild(textarea);
                    textarea.select();
                    try {
                        document.execCommand('copy');
                        this.notifySuccess('Table copied');
                    } catch (err) {
                        this.notifyError('Failed to copy table');
                    }
                    document.body.removeChild(textarea);
                }
            },

            focusFilterBoxInput() {
                switch (this.filterAdding.selectedOperator) {
                    case 'btwn':
                        this.setFocusElementById('inputFilterBetweenValueFrom', true)
                        break;
                    default:
                        this.setFocusElementById('inputFilterValue', true)
                        break;
                }
            },

            updateFilter(data) {
                this.setUrlParameter('filter[' + data.filter_name + ']', data.filter_value);
            },

            showFilterModal(field){
                this.$bvModal.hide('show-hide-columns-local-modal');

                this.buildFiltersFromUrl();

                if(['date', 'datetime'].includes(field.type)) {
                    let selectedField = field.name ? this.fields.find(f => f.name === field.name) : null;
                    let existingFilter = this.filters.find(f => f.name === selectedField.name);
                    this.$root.$emit('show::modal::date-selector-widget', {field, existingFilter, callback: this.updateFilter});
                    return;
                }

                this.setFilterAdding(field.name);
                this.$bvModal.show('filter-box-modal')
            },

            editFilter(urlParameter) {
                this.buildFiltersFromUrl();

                let filterName = urlParameter.split('[')[1].split(']')[0];
                let fieldName = filterName.replaceAll('_equal','')
                    .replaceAll('_contains','')
                    .replaceAll('_between','')
                    .replaceAll('_lower_than','')
                    .replaceAll('_greater_than','')
                    .replaceAll('_starts_with','')
                    .replaceAll('_not_starts_with','');

                let field = this.findField(fieldName);
                if(field){
                    this.showFilterModal(field);
                }
            },

            setFilterAdding(fieldName = null) {
                if (this.fields.length === 0) {
                    return;
                }
                let selectedField = fieldName ? this.fields.find(f => f.name === fieldName) : this.fields[0];
                let existingFilter = this.filters.find(f => f.name === selectedField.name);
                let defaultOperator = selectedField.operators[0];
                let defaultFilterValueMin = '';
                let defaultFilterValueMax = '';

                switch (selectedField.type) {
                    case 'numeric':
                    case 'integer':
                        defaultOperator = 'greater than';
                        defaultFilterValueMin = 0;
                        defaultFilterValueMax = 0;
                        break;
                    case 'string':
                        defaultOperator = 'equals';
                        defaultFilterValueMin = '';
                        defaultFilterValueMax = '';
                        break;
                    case 'date':
                        defaultOperator = 'btwn';
                        defaultFilterValueMin = moment().startOf("year").format('YYYY-MM-DD HH:mm');
                        defaultFilterValueMax = moment().endOf("day").format('YYYY-MM-DD HH:mm');
                        break;
                    case 'datetime':
                        defaultOperator = 'btwn';
                        defaultFilterValueMin = moment().startOf("day").format('YYYY-MM-DD HH:mm');
                        defaultFilterValueMax = moment().endOf("day").format('YYYY-MM-DD HH:mm');
                        break;
                    default:
                        defaultOperator = 'contains';
                        defaultFilterValueMin = '';
                        defaultFilterValueMax = '';
                }

                let selectedOperator = existingFilter ? existingFilter.selectedOperator : defaultOperator;
                let filterValueMin = existingFilter ? existingFilter.value : defaultFilterValueMin;
                let filterValueMax = existingFilter ? existingFilter.valueBetween : defaultFilterValueMax;

                this.filterAdding = {
                    fields: this.fields,
                    selectedField: selectedField,
                    operators: selectedField.operators,
                    selectedOperator: selectedOperator,
                    value: filterValueMin,
                    valueBetween: filterValueMax,
                }
            },

            buildFiltersFromUrl() {
                const urlParams = new URLSearchParams(window.location.search);

                this.filters = [];

                for (const [key, value] of urlParams.entries()) {
                    if(key.startsWith('filter')) {
                        let filterName = key.split('[')[1].split(']')[0];

                        let fieldName = filterName;
                        fieldName = fieldName.replaceAll('_equal','');
                        fieldName = fieldName.replaceAll('_contains','');
                        fieldName = fieldName.replaceAll('_between','');
                        fieldName = fieldName.replaceAll('_lower_than','');
                        fieldName = fieldName.replaceAll('_greater_than','');
                        fieldName = fieldName.replaceAll('_starts_with','');
                        fieldName = fieldName.replaceAll('_not_starts_with','');

                        let filterOperator = filterName.replace(fieldName, '');
                        let operatorKey;

                        switch (filterOperator) {
                          case '':
                          case '_equal':
                            operatorKey = 'equals';
                            break;
                          case '_contains':
                            operatorKey = 'contains';
                            break;
                          case '_between':
                            operatorKey = 'btwn';
                            break;
                          case '_greater_than':
                            operatorKey = 'greater than';
                            break;
                          case '_lower_than':
                            operatorKey = 'lower than';
                            break;
                          case '_starts_with':
                            operatorKey = 'starts with';
                            break;
                          case '_not_starts_with':
                            operatorKey = 'not starts with';
                            break;
                          default:
                            operatorKey = filterOperator;
                        }

                        let filter = {
                            name: fieldName,
                            displayName: '', //field.display_name,
                            selectedOperator: operatorKey,
                            value: value,
                            valueBetween: '',
                        }

                        if(filterOperator === '_between') {
                            let values = Array.isArray(value) ? value : value.split(',');
                            filter.value = values[0];
                            filter.valueBetween = values[1];
                        }

                        this.filters.push(filter);
                    }
                }
            },

            addFilter() {
                console.log('addFilter');
                this.$bvModal.hide('filter-box-modal');

                const { value, selectedOperator, valueBetween, selectedField } = this.filterAdding;

                let filterName = '';
                let filterValue = '';

                switch (selectedOperator) {
                  case 'equals':
                    filterName = ['filter[', selectedField.name, ']'].join('');
                    filterValue = `${value}`;
                    break;
                  case 'btwn':
                    filterName = ['filter[', selectedField.name, '_between]'].join('');
                    filterValue = `${value},${valueBetween}`;
                    break;
                  case 'greater than':
                    filterName = `filter[${selectedField.name}_greater_than]`;
                    filterValue = value;
                    break;
                  case 'lower than':
                    filterName = `filter[${selectedField.name}_lower_than]`;
                    filterValue = value;
                    break;
                  case 'starts with':
                    filterName = `filter[${selectedField.name}_starts_with]`;
                    filterValue = value;
                    break;
                  case 'not starts with':
                    filterName = `filter[${selectedField.name}_not_starts_with]`;
                    filterValue = value;
                    break;
                  default:
                    filterName = `filter[${selectedField.name}_${selectedOperator}]`;
                    filterValue = value;
                }

                this.setUrlParameter(filterName, filterValue);
            },

            findColumn(fieldName) {
                return this.columns.find(column => column.name === fieldName);
            },


            findField(fieldName) {
                return this.fields.find(f => f.name === fieldName);
            },

            reloadRecords() {
                this.page = 1;
                this.records = [];
                this.loadRecords();
            },

            loadRecords() {
                this.showLoading();

                const urlParams = JSON.parse(JSON.stringify(this.$router.currentRoute.query));
                urlParams['filename'] = 'data.json';
                urlParams['page'] = this.page;
                urlParams['per_page'] = this.perPage;

                // merge custom params
                for (const [key, value] of Object.entries(this.params)) {
                    urlParams[key] = value;
                }

                let url1 = this.reportUrl ?? this.$router.currentRoute.path;

                this.getReportData(url1, urlParams)
                    .then(response => {
                        if(this.page === 1) {
                            this.records = [];
                        }
                        this.lastResponse = response.data;

                        this.records = this.records.concat(this.lastResponse.data);
                        this.columns = this.lastResponse.meta['columns'];
                        this.fields = this.lastResponse.meta['field_links'];
                        this.meta = this.lastResponse.meta;
                        
                        // Apply column order from URL if present
                        this.applyColumnOrder();


                        this.hasMoreRecords = response.data.data.length === this.perPage;
                        if (this.records.length > 0) {
                            this.visibleFields = Object.keys(this.records[0])
                                .map(this.findField)
                                .filter(f => f);

                            // Get visible columns in the order they appear in this.columns
                            const recordKeys = Object.keys(this.records[0]);
                            this.visibleColumns = this.columns
                                .filter(col => recordKeys.includes(col.name) && col.visible);
                        }

                        let reportData = {
                            columns: this.columns,
                            records: this.records,
                            meta: this.meta,
                        };

                        this.$emit('dataChanged', reportData);
                    })
                    .catch(error => {
                        this.displayApiCallError(error);
                    })
                    .finally(() => {
                        this.hideLoading();
                    });
            },

            loadMoreRecords(){
                if (helpers.isMoreThanPercentageScrolled(70) && this.hasMoreRecords && !this.isLoading) {
                    this.page++;

                    this.loadRecords();
                }
            },

            getReportData(url, params) {
                return axios.get(url, {params: params})
            },

            isUrlSortedBy(field) {
                return this.getUrlParameter('sort', '').includes(field);
            },

            showShowHideColumnsModal() {
                this.$bvModal.show('show-hide-columns-local-modal');
            },
            
            applyColumnOrder() {
                const orderParam = this.getUrlParameter('order', '');
                if (!orderParam) {
                    return;
                }
                
                const orderedNames = orderParam.split(',');
                const columnMap = {};
                
                // Create a map of column names to column objects
                this.columns.forEach(col => {
                    columnMap[col.name] = col;
                });
                
                // Reorder columns based on the order parameter
                const orderedColumns = [];
                orderedNames.forEach(name => {
                    if (columnMap[name]) {
                        orderedColumns.push(columnMap[name]);
                        delete columnMap[name];
                    }
                });
                
                // Add any remaining columns that weren't in the order parameter
                Object.values(columnMap).forEach(col => {
                    orderedColumns.push(col);
                });
                
                this.columns = orderedColumns;
            },

            showMemorizeReportModal() {
                this.$bvModal.hide('quick-actions-modal');
                this.$bvModal.show('memorize-report-modal');
                this.setFocusElementById('memorize-name');
                this.memorizeName = '';
            },

            async memorizeReport() {
                const isValid = await this.$refs.form.validate();
                if (isValid) {
                    this.showLoading();
                    this.apiPostNavigationMenu({
                        name: this.memorizeName,
                        url: this.$router.currentRoute.fullPath,
                        group: 'reports',
                    })
                        .then(() => {
                            this.notifySuccess(this.$t('Report has been saved'));
                        })
                        .catch((error) => {
                            if (error.response && error.response.status === 422) {
                                this.$refs.form.setErrors(error.response.data.errors);
                            } else if (error.response && error.response.status === 403) {
                                this.notifyError(this.$t('You are not allowed to memorize reports.'));
                            } else {
                                this.displayApiCallError(error);
                            }
                        })
                        .finally(() => {
                            this.hideLoading();
                            this.$bvModal.hide('memorize-report-modal');
                        });
                }
            },

            presetSelected(value) {
                this.showDateFilter = false;
                this.showDateFilter = true;
                switch (value) {
                    case 'today':
                        this.filterAdding.value = this.$t('today'),
                        this.filterAdding.valueBetween = this.$t('today 23:59:59')
                        break;
                    case 'yesterday':
                        this.filterAdding.value = this.$t('yesterday'),
                        this.filterAdding.valueBetween = this.$t('yesterday 23:59:59')
                        break;
                    case 'this_week':
                        this.filterAdding.value = this.$t('monday this week'),
                        this.filterAdding.valueBetween = this.$t('sunday this week 23:59:59')
                        break;
                    case 'last_week':
                        this.filterAdding.value = this.$t('monday last week');
                        this.filterAdding.valueBetween = this.$t('sunday last week 23:59:59')
                        break;
                    case 'last_month':
                        this.filterAdding.value = this.$t('first day of last month');
                        this.filterAdding.valueBetween = this.$t('last day of last month 23:59:59')
                        break;
                    case 'last_year':
                        this.filterAdding.value = this.$t('first day of January this year');
                        this.filterAdding.valueBetween = this.$t('last day of December this year 23:59:59')
                        break;
                    case 'custom':
                        this.showDateFilter = true;
                        break;

                    default:
                        break;
                }
            },

            showScheduledReportCreateModal() {
                this.$bvModal.hide('quick-actions-modal');
                this.$bvModal.show('scheduled-report-create-modal');
            }
        },

        computed: {
            helpers() {
                return helpers
            },

            isUrlSortDesc() {
                return this.getUrlParameter('sort', ' ').startsWith('-')
            },

            breadcrumbs() {
                return this.$router.currentRoute.path
                    .replace('/', '')
                    .replaceAll('/', ' > ')
                    .replaceAll('-', ' ');
            }
        }
    }

    </script>

<style scoped>

.dropdown > .btn.dropdown-toggle {
    font-size: 12px;
    padding: 0;
    color: black;
    font-weight: bold;
    &:focus, &:active {
        outline: none;
        box-shadow: none;
        border-color: transparent;
    }
}

.dropdown-toggle::after {
    display: none;
}

::-webkit-scrollbar{
    height: 4px;
    width: 4px;
}

::-webkit-scrollbar-thumb:horizontal{
    background: lightgray;
    border-radius: 10px;
}

::-webkit-scrollbar:horizontal{
    height: 8px;
    background: none;
}

</style>
