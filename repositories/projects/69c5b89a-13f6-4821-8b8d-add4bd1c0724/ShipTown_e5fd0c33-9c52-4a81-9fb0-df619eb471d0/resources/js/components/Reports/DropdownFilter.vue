<template>
    <div class="dropdown" ref="outerDropdown">
        <button type="button" class="btn btn-primary ml-2 dropdown-toggle" style="width: 42px;" @click="toggleDropdown('outer')">
            <font-awesome-icon icon="sliders" class="fa-lg" role="button"></font-awesome-icon>
        </button>
        <div class="dropdown-menu dropdown-menu-right" :class="{ show: isOuterDropdownOpen }">
            <div class="dropdown-submenu">
                <div class="dropdown dropleft" v-for="field in fields" :key="field.name">
                    <button class="btn btn-link dropdown-toggle field" @click.stop="toggleDropdown(field.name)">
                        {{ $t(field.display_name) }}<font-awesome-icon v-if="isUrlSortedBy(field['name'])" :icon="isUrlSortDesc ? 'caret-down' : 'caret-up'" class="fa-xs" role="button"></font-awesome-icon>
                    </button>
                    <div class="dropdown-menu" :class="{ show: nestedDropdownOpened === field.name }">
                        <button class="dropdown-item" type="button" @click="setUrlParameterAngGo('sort',['-', field.name].join(''))">
                            <icon-sort-desc class="mr-1" /> {{ $t('Sort Descending') }}
                        </button>
                        <button class="dropdown-item" type="button" @click="setUrlParameterAngGo('sort', field.name)">
                            <icon-sort-asc class="mr-1" /> {{ $t('Sort Ascending') }}
                        </button>
                        <button class="dropdown-item" type="button" @click="showFilterModal(field)">
                            <icon-filter class="mr-1" /> {{ $t('Filter by value') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import url from "../../mixins/url";
import helpers from "../../mixins/helpers";

import moment from "moment";
import IconSortAsc from "../UI/Icons/IconSortAsc.vue";
import IconSortDesc from "../UI/Icons/IconSortDesc.vue";
import IconFilter from "../UI/Icons/IconFilter.vue";
import ModalDateBetweenSelector from "../Widgets/ModalDateBetweenSelector.vue";

export default {
    name: "DropdownFilter",

    components: {
        IconSortAsc,
        IconSortDesc,
        IconFilter,
        ModalDateBetweenSelector,
    },

    mixins: [url, helpers],

    data() {
        return {
            isOuterDropdownOpen: false,
            nestedDropdownOpened: null,
            selected: [], // Must be an array reference!
            filters: [],
            filterAdding: null,
        };
    },

    mounted() {
        this.buildFiltersFromUrl();
        document.addEventListener("click", this.handleClickOutsideDropdownFilter);
    },

    beforeDestroy() {
        document.removeEventListener("click", this.handleClickOutsideDropdownFilter);
    },

    beforeMount() {
        this.setFilterAdding()
    },

    methods: {
        toggleDropdown(type) {
            if (type === "outer") {
                this.isOuterDropdownOpen = !this.isOuterDropdownOpen;
                this.nestedDropdownOpened = '';
            } else {
                this.nestedDropdownOpened = type;
            }
        },

        handleClickOutsideDropdownFilter(event) {
            if (
                this.$refs.outerDropdown &&
                !this.$refs.outerDropdown.contains(event.target)
            ) {
                this.isOuterDropdownOpen = false;
                this.nestedDropdownOpened = '';
            }
        },

        isUrlSortedBy(field) {
            return this.getUrlParameter("sort", "").includes(field);
        },

        isUrlSortDesc() {
            return this.getUrlParameter("sort", " ").startsWith("-");
        },

        showFilterModal(field) {
            this.$emit("showFilterModal", (field));
        },

        setFilterAdding(fieldName = null) {
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

            for (const [key, value] of urlParams.entries()) {
                if (key.startsWith("filter")) {
                    let filterName = key.split("[")[1].split("]")[0];

                    let fieldName = filterName;
                    fieldName = fieldName.replaceAll("_equal", "");
                    fieldName = fieldName.replaceAll("_contains", "");
                    fieldName = fieldName.replaceAll("_between", "");
                    fieldName = fieldName.replaceAll("_lower_than", "");
                    fieldName = fieldName.replaceAll("_greater_than", "");
                    fieldName = fieldName.replaceAll("_starts_with", "");
                    fieldName = fieldName.replaceAll("_not_starts_with", "");

                    let filterOperator = filterName.replace(fieldName, "");
                    let filterOperatorHumanString = filterOperator;

                    switch (filterOperator) {
                        case "":
                            filterOperatorHumanString = "equals";
                            break;
                        case "_equal":
                            filterOperatorHumanString = "equals";
                            break;
                        case "_contains":
                            filterOperatorHumanString = "contains";
                            break;
                        case "_between":
                            filterOperatorHumanString = "between";
                            break;
                        case "_greater_than":
                            filterOperatorHumanString = "greater than";
                            break;
                        case "_lower_than":
                            filterOperatorHumanString = "lower than";
                            break;
                        case "_starts_with":
                            filterOperatorHumanString = "starts with";
                            break;
                        case "not_starts_with":
                            filterOperatorHumanString = "not starts with";
                            break;
                        default:
                            filterOperatorHumanString = filterOperator;
                    }

                    let filter = {
                        name: fieldName,
                        displayName: "", //field.display_name,
                        selectedOperator:
                            filterOperator === "_between"
                                ? "btwn"
                                : filterOperatorHumanString,
                        value: value,
                        valueBetween: "",
                    };

                    if (filterOperator === "_between") {
                        let values = Array.isArray(value)
                            ? value
                            : value.split(",");
                        filter.value = values[0];
                        filter.valueBetween = values[1];
                    }

                    this.filters.push(filter);
                }
            }
        },

        addFilter() {
            const { value, selectedOperator, valueBetween, selectedField } =
                this.filterAdding;

            let filterName = "";
            let filterValue = "";

            switch (selectedOperator) {
                case "equals":
                    filterName = ["filter[", selectedField.name, "]"].join("");
                    filterValue = `${value}`;
                    break;
                case "btwn":
                    filterName = [
                        "filter[",
                        selectedField.name,
                        "_between]",
                    ].join("");
                    filterValue = `${value},${valueBetween}`;
                    break;
                case "greater than":
                    filterName = `filter[${selectedField.name}_greater_than]`;
                    filterValue = value;
                    break;
                case "lower than":
                    filterName = `filter[${selectedField.name}_lower_than]`;
                    filterValue = value;
                    break;
                case "starts with":
                    filterName = `filter[${selectedField.name}_starts_with]`;
                    filterValue = value;
                    break;
                case "not starts with":
                    filterName = `filter[${selectedField.name}_not_starts_with]`;
                    filterValue = value;
                    break;
                default:
                    filterName = `filter[${selectedField.name}_${selectedOperator}]`;
                    filterValue = value;
            }

            this.setUrlParameterAngGo(filterName, filterValue);
        },
    },

    props: {
        fields: {
            type: Array,
            required: true,
        },
    },
};
</script>

<style lang="scss" scoped>
.dropdown > .btn.dropdown-toggle.field {
    font-size: 12px;
    padding: 4px;
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
