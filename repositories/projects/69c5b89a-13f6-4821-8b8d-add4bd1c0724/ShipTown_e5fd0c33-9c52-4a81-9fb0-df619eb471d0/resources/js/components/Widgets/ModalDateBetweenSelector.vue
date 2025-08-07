<template>
    <b-modal id="modal-date-selector-widget" size="sm" no-fade hide-header>
        <div v-if="field" class="modal-header pb-1">
            <h6 class="modal-title w-100 text-center">{{ $t(field.display_name) }}</h6>
        </div>
        <form class="form" @submit.prevent="">
            <div class="form-group">
                <label class="form-label" for="starting_date">{{ $t('Select Range') }}</label>
                <select id="modal-date-between-filter-select-date-range" class="form-control" dusk="select-preset-date" @change="updateDates" v-model="selectedDateRange">
                    <option v-for="preset in presets" :id="'modal-date-between-filter-select-date-range-' + preset.value" :value="preset.value" :key="preset.value">{{ preset.label }}</option>
                </select>
            </div>
            <div v-if="showDateFilter">
                <div class="form-group">
                    <label class="form-label" for="starting_date">{{ $t('From') }}</label>
                    <input class="form-control" id="starting_date" type="datetime-local" @change="updateFilterValue" v-model="new_from_datetime">
                </div>
                <div class="form-group">
                    <label class="form-label" for="ending_date">{{ $t('To') }}</label>
                    <input class="form-control" id="ending_date" type="datetime-local" @change="updateFilterValue" v-model="new_to_datetime">
                </div>
            </div>
        </form>
        <template #modal-footer>
            <b-button b-button variant="secondary" class="float-right" @click="$bvModal.hide('modal-date-selector-widget')">{{ $t('Cancel') }}</b-button>
            <b-button variant="primary" class="float-right" @click="apply" id="modal-date-between-filter-apply" >{{ $t('Apply') }}</b-button>
        </template>
    </b-modal>
</template>

<script>
import moment from 'moment';
export default {
    props: {
        from_datetime: {
            type: String,
            default: moment().startOf('day').format('YYYY-MM-DDTHH:mm')
        },
        to_datetime: {
            type: String,
            default: moment().endOf('day').format('YYYY-MM-DDTHH:mm')
        },
        showDateFilter: {
            type: Boolean,
            default: true
        }
    },

    mounted() {
        // listen for the event to show the modal
        this.$root.$on('show::modal::date-selector-widget', (data) => {
            this.field = data.field;
            this.callback = data.callback;

            if (data.existingFilter) {
                this.selectedDateRange = 'custom';
                this.new_from_datetime = moment(data.existingFilter.value).format('YYYY-MM-DDTHH:mm');
                this.new_to_datetime = moment(data.existingFilter.valueBetween).format('YYYY-MM-DDTHH:mm');
            } else {
                this.selectedDateRange = 'today';
                this.new_from_datetime = this.from_datetime;
                this.new_to_datetime = this.to_datetime;
            }

            this.updateDates();
            this.$bvModal.show('modal-date-selector-widget');
        });
    },

    data: function () {
        return {
            callback: null,
            filterValue: null,
            field: null,
            selectedDateRange: 'today',
            new_from_datetime: null,
            new_to_datetime: null,
            presets: [
                { value:'custom', label: this.$t('Custom')},
                { value:'today', label: this.$t('Today')},
                { value:'yesterday', label: this.$t('Yesterday')},
                { value:'this_week', label: this.$t('This Week')},
                { value:'last_week', label: this.$t('Last Week')},
                { value:'this_month', label: this.$t('This Month')},
                { value:'last_month', label: this.$t('Last Month')},
                { value:'this_year', label: this.$t('This Year')},
                { value:'last_year', label: this.$t('Last Year')}
            ]
        }
    },
    methods: {
        updateFilterValue: function () {
            this.filterValue = this.new_from_datetime + ',' + this.new_to_datetime;
        },

        apply: function () {
            if (this.callback) {
                let newVar = {
                    filter_name: this.field.name + '_between',
                    filter_value: this.filterValue,
                };
                this.callback(newVar);
            }

            this.$emit('apply', {
                from_datetime: this.new_from_datetime,
                to_datetime: this.new_to_datetime,
            });

            this.$bvModal.hide('modal-date-selector-widget');
        },

        updateDates: function () {
            switch (this.selectedDateRange) {
                case 'today':
                    this.new_from_datetime = moment().startOf('day').format('YYYY-MM-DDTHH:mm');
                    this.new_to_datetime = moment().endOf('day').format('YYYY-MM-DDTHH:mm');
                    this.filterValue = 'today,today 23:59:59';
                    break;
                case 'yesterday':
                    this.new_from_datetime = moment().subtract(1, 'days').startOf('day').format('YYYY-MM-DDTHH:mm');
                    this.new_to_datetime = moment().subtract(1, 'days').endOf('day').format('YYYY-MM-DDTHH:mm');
                    this.filterValue = 'yesterday,yesterday 23:59:59';
                    break;
                case 'this_week':
                    this.new_from_datetime = moment().startOf('week').format('YYYY-MM-DDTHH:mm');
                    this.new_to_datetime = moment().endOf('week').format('YYYY-MM-DDTHH:mm');
                    this.filterValue = 'this week monday,this week sunday 23:59:59';
                    break;
                case 'last_week':
                    this.new_from_datetime = moment().subtract(1, 'weeks').startOf('week').format('YYYY-MM-DDTHH:mm');
                    this.new_to_datetime = moment().subtract(1, 'weeks').endOf('week').format('YYYY-MM-DDTHH:mm');
                    this.filterValue = 'last week monday,last week sunday 23:59:59';
                    break;
                case 'this_month':
                    this.new_from_datetime = moment().startOf('month').format('YYYY-MM-DDTHH:mm');
                    this.new_to_datetime = moment().endOf('month').format('YYYY-MM-DDTHH:mm');
                    this.filterValue = this.new_from_datetime + ',' + this.new_to_datetime;
                    break;
                case 'last_month':
                    this.new_from_datetime = moment().subtract(1, 'months').startOf('month').format('YYYY-MM-DDTHH:mm');
                    this.new_to_datetime = moment().subtract(1, 'months').endOf('month').format('YYYY-MM-DDTHH:mm');
                    this.filterValue = this.new_from_datetime + ',' + this.new_to_datetime;
                    break;
                case 'this_year':
                    this.new_from_datetime = moment().startOf('year').format('YYYY-MM-DDTHH:mm');
                    this.new_to_datetime = moment().endOf('year').format('YYYY-MM-DDTHH:mm');
                    this.filterValue = this.new_from_datetime + ',' + this.new_to_datetime;
                    break;
                case 'last_year':
                    this.new_from_datetime = moment().subtract(1, 'years').startOf('year').format('YYYY-MM-DDTHH:mm');
                    this.new_to_datetime = moment().subtract(1, 'years').endOf('year').format('YYYY-MM-DDTHH:mm');
                    this.filterValue = this.new_from_datetime + ',' + this.new_to_datetime;
                    break;
                case 'custom':
                    this.filterValue = this.new_from_datetime + ',' + this.new_to_datetime;
                    break;
            }
        }
    }
}
</script>
