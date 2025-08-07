<template>
    <div>
        <search-and-option-bar-observer/>
        <search-and-option-bar :isStickable="true">
            <barcode-input-field :placeholder="$t('search')" :url_param_name="'search'" @barcodeScanned="loadScheduledReports" autofocus></barcode-input-field>
            <template v-slot:buttons>
                <top-nav-button v-b-modal="'scheduled-reports-options-modal'"/>
            </template>
        </search-and-option-bar>

        <div v-if="scheduledReports.length === 0" class="text-secondary small text-center pt-2">
            {{ $t('No records found') }}<br><br>
            {{ $t('To schedule report, please open report') }}
            {{ $t('add required filters') }}
            {{ $t('and use "Schedule Report" function from Options menu') }}
        </div>

        <div class="list-group mt-2">
            <div class="setting-list" v-for="scheduledReport in scheduledReports">
                <div class="card p-2 flex-fill">
                    <div class="row">
                        <div class="col-8 cursor-pointer"
                             @click="edit(scheduledReport)"
                             :id="`scheduled-report-${scheduledReport.id}`"
                             :key="`scheduled-report-${scheduledReport.id}`"
                             :dusk="`scheduled-report-${scheduledReport.id}`">
                            <div class="setting-title">{{ scheduledReport.name }}</div>
                            <div class="setting-desc">{{ scheduledReport.email }}</div>
                            <div class="setting-desc">{{ $t('Next run: ') }} {{ getNextRunAt(scheduledReport) }}</div>
                        </div>
                        <div class="col-4 text-right align-content-center">
                            <a :href="scheduledReport.uri" class="btn btn-outline-primary" target="_blank">{{ $t('PREVIEW') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <scheduled-report-edit-modal :scheduledReport="selectedScheduledReport" @updated="scheduledReportUpdated"></scheduled-report-edit-modal>
    </div>
</template>

<script>
import api from "../../mixins/api";
import helpers from "../../mixins/helpers";
import url from "../../mixins/url";
import SearchFilter from "../UI/SearchFilter.vue";
import ScheduledReportEditModal from './ScheduledReportEditModal.vue'
import moment from "moment/moment";

import cronstrue from "cronstrue";

export default {
    mixins: [api, helpers, url],

    components: {
        SearchFilter,
        ScheduledReportEditModal,
        moment
    },

    created() {
        this.loadScheduledReports();
    },

    data: () => ({
        error: false,
        scheduledReports: [],
        selectedScheduledReport: null
    }),

    methods: {
        loadScheduledReports() {
            const params = {
                'filter[name]': this.getUrlParameter('search'),
                'sort': 'next_run_at',
                'per_page': 999
            }
            this.apiGetScheduledReport(params)
                .then(({ data }) => {
                    this.scheduledReports = data.data;
                }).catch((error) => {
                    this.displayApiCallError(error);
                });
        },

        getNextRunAt(scheduledReport) {
            return moment(scheduledReport.next_run_at).format('YYYY-MM-DD HH:mm');
        },

        searchSchedule(q) {
            this.setUrlParameter('filter[name]', q);
            this.loadScheduledReports();
        },

        edit(scheduledReport) {
            this.selectedScheduledReport = scheduledReport;
            this.$bvModal.show('scheduled-report-edit-modal');
        },

        scheduledReportUpdated() {
            this.loadScheduledReports()
        },
    }
}
</script>
