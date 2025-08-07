<template>
    <!--
    style="transform: rotateX(180deg);"
    this is used twice to move scrollbar to the top of the table
    -->
    <div class="table-responsive py-2" style="transform: rotateX(180deg);">
        <table class="table-hover w-100 text-left small text-nowrap" style="transform: rotateX(180deg);">
            <thead>
                <tr>
                    <th v-for="column in columns" class="small pr-2 align-bottom">
<!--                        how to make it to be on top of every component?-->
                        <div class="dropdown" style="position: relative; z-index: 1000;" data-boundary="viewport">
                            <button class="w-100 btn btn-link dropdown-toggle text-left text-wrap" :class="{'text-right': ['numeric', 'integer', 'boolean'].includes(column.type)}" id="dropdownMenu2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                {{ column['display_name'] }}
                                <font-awesome-icon v-if="isUrlSortedBy(column.name)" :icon="isUrlSortDesc ? 'caret-down' : 'caret-up'" class="fa-xs" role="button"></font-awesome-icon>
                            </button>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenu2">
                                <button class="dropdown-item" type="button" @click="setUrlParameter('sort',  ['-', column.name].join(''))">
                                    <icon-sort-desc class="mr-1"/> {{ $t('Sort Descending') }}
                                </button>
                                <button class="dropdown-item" type="button" @click="setUrlParameter('sort', column.name)">
                                    <icon-sort-asc class="mr-1"/> {{ $t('Sort Ascending') }}
                                </button>
                                <button class="dropdown-item" type="button" @click="showFilterModal(column)">
                                    <icon-filter class="mr-1"/> {{ $t('Filter by value') }}
                                </button>
                            </div>
                        </div>
                    </th>
                </tr>
            </thead>
            <tbody>
            <tr class="table-hover align-text-top" v-for="record in records">
                <template v-for="field in columns" v-if="field">
                    <td v-if="field.type === 'datetime'" class="pr-3" @click="emitClickEvent(record, field.name)">{{ formatDateTime(record[field.name], 'YYYY MMM D HH:mm') }}</td>
                    <td v-else-if="field.type === 'date'" class="pr-3" @click="emitClickEvent(record, field.name)">{{ formatDateTime(record[field.name], 'YYYY MMM D') }}</td>
                    <td v-else-if="['numeric', 'money'].includes(field.type)" class="pr-2 text-right" :data-value="record[field.name]" @click="emitClickEvent(record, field.name)">{{ toNumberOrDash(record[field.name], 0) }}</td>
                    <td v-else-if="['integer'].includes(field.type)" class="pr-1 text-right" :data-value="record[field.name]" @click="emitClickEvent(record, field.name)">{{ record[field.name] }}</td>
                    <td v-else-if="field.type === 'boolean'" class="pr-3 text-right" :data-value="record[field.name]" @click="emitClickEvent(record, field.name)">{{ toNumberOrDash(record[field.name], 0) }}</td>
                    <td v-else-if="field.type === 'url'" class="pr-3"><a :href="record[field.name]" class="font-weight-bold">{{ record[field.name] }}</a></td>
                    <td v-else-if="field.type === 'json'" class="pr-3" @click="emitClickEvent(record, field.name)">
                        <pre class="mb-0" style="white-space: pre-wrap; word-break: break-word;">
                            {{ formatJson(record[field.name]) }}
                        </pre>
                    </td>
                    <td v-else class="pr-3 overflow-hidden mr-1" style="max-width: 200px;" >
                        <template v-if="['product_sku', 'sku'].includes(field.name)">
                            <product-sku-button :product_sku="record[field.name]"/>
                        </template>
                        <template v-else-if="['order_number'].includes(field.name)">
                            <a :href="'/orders?search=' + record[field.name]" target="_blank">{{ record[field.name] }}</a>
                        </template>
                        <template v-else>
                            <div @click="emitClickEvent(record, field.name)" class="w-auto" :title="record[field.name]">{{ record[field.name] }}</div>
                        </template>
                    </td>
                </template>
            </tr>
            </tbody>
            <slot></slot>
        </table>
    </div>
</template>
<script>
import IconSortDesc from "../UI/Icons/IconSortDesc.vue";
import url from "../../mixins/url.vue";
import IconSortAsc from "../UI/Icons/IconSortAsc.vue";
import IconFilter from "../UI/Icons/IconFilter.vue";
import ProductSkuButton from "../SharedComponents/ProductSkuButton.vue";
import helpers from "../../helpers";
import api from "../../mixins/api.vue";

export default {
    name: 'report-table',
    components: {ProductSkuButton, IconFilter, IconSortAsc, IconSortDesc},
    mixins: [url, helpers, api],
    props: {
        data: Array,
        records: Array,
        columns: {
            type: Array,
            default: [],
        },
    },

    computed: {
        isUrlSortDesc() {
            return this.getUrlParameter('sort', ' ').startsWith('-')
        },
    },

    methods: {
        emitClickEvent(record, column_name) {
            this.$emit('click', record, column_name)
        },

        isUrlSortedBy(columnName) {
            return this.getUrlParameter('sort', '').includes(columnName);
        },
        showFilterModal(field){
            this.$emit("showFilterModal", (field));
        },
    },
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

::-webkit-scrollbar {
  height: 4px;
  width: 4px;
}

::-webkit-scrollbar-thumb:horizontal {
  background: lightgray;
  border-radius: 10px;
}

::-webkit-scrollbar:horizontal {
  height: 8px;
  background: none;
}

</style>
