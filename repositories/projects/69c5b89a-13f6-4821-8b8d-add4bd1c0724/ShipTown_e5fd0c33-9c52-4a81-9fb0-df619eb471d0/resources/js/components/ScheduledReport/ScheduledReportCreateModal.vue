<template>
    <b-modal :title="$t('Add Scheduled Report')" id="scheduled-report-create-modal" @shown="reset()" no-fade>
        <ValidationObserver ref="createScheduleReportForm">
            <form class="form" ref="loadingContainer" @submit.prevent="submit">
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label" for="name">{{ $t('Report Name') }}</label>
                    <div class="col-sm-9">
                        <input v-model="name" class="form-control" id="schedule-report-modal-name-input" required>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-form-label" for="scheduled-email">{{ $t('Email') }}</label>
                    <div class="col-sm-9">
                            <input v-model="email" :class="{
                                'form-control': true,
                            }" id="schedule-report-modal-email-input" type="email" dusk="input-email" required>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-8">
                        <label class="col-sm-12 col-form-label">{{ $t('Schedule') }}</label>
                        <button v-for="schedule, key in quickSchedules" type="button" class="btn btn-sm btn-outline-primary mr-1 font-weight-bold"
                            :key="key"
                            :class="{ 'active': selectedSchedule === key }"
                            @click="setSchedule(key)"
                        >
                            {{ $t(schedule) }}
                        </button>
                    </div>
                    <div class="col-4">
                        <label class="col-sm-12 col-form-label text-capitalize" :for="`time-unit-hour`">{{ $t('hour') }}</label>
                        <div class="col-sm-12 small">
                            <select v-model="timeUnits['hour']" class="form-control custom-select-sm small" :id="`time-unit-hour`" required>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                                <option value="6">6</option>
                                <option value="7">7</option>
                                <option value="8">8</option>
                                <option value="9">9</option>
                                <option value="10">10</option>
                                <option value="11">11</option>
                                <option value="12">12</option>
                                <option value="13">13</option>
                                <option value="14">14</option>
                                <option value="15">15</option>
                                <option value="16">16</option>
                                <option value="17">17</option>
                                <option value="18">18</option>
                                <option value="19">19</option>
                                <option value="20">20</option>
                                <option value="21">21</option>
                                <option value="22">22</option>
                                <option value="23">23</option>
                                <option value="0">24</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mb-3 text-small">
                    <label v-for="time, index in nextRunTimes" :key=index class="form-label">
                        {{ time }}
                    </label>
                </div>

            </form>
        </ValidationObserver>
        <template #modal-footer>
            <b-button variant="secondary" class="float-right" @click="$bvModal.hide('scheduled-report-create-modal');">
                {{ $t('Cancel') }}
            </b-button>
            <b-button id="schedule-report-modal-ok-button" variant="primary" class="float-right" @click="createScheduledReport" dusk="btn-submit">
                {{ $t('SCHEDULE') }}
            </b-button>
        </template>
    </b-modal>
</template>

<script>
import cronstrue from "cronstrue";
import { parseExpression } from "cron-parser";
import { ValidationObserver, ValidationProvider } from "vee-validate";

import loadingOverlay from '../../mixins/loading-overlay';
import api from "../../mixins/api";
import helpers from "../../helpers";
import Modals from "../../plugins/Modals";

export default {
    name: "ScheduledReportCreateModal",
    components: {
        ValidationObserver,
        ValidationProvider,
    },
    mixins: [loadingOverlay, api, helpers],
    data() {
        return {
            name: "",
            email: "",
            selectedSchedule: "weekly",
            timeUnits: {
                minute: "0",
                hour: "0",
                day: "*",
                month: "*",
                week: "1",
            },
            nextRunTimes: [],
            quickSchedules: {
                // hourly: this.$t('Hourly'),
                daily: this.$t('Daily'),
                weekly: this.$t('Weekly'),
                monthly: this.$t('Monthly'),
                yearly: this.$t('Yearly'),
            },
            tooltips: {
                hour: "Allowed values: 0-23, */2 (every 2 hours), 9-17 (from 9 AM to 5 PM)",
                day: "Allowed values: 1-31, */3 (every 3 days), 1,15 (on the 1st and 15th)",
                month: "Allowed values: 1-12, */3 (every 3 months), 1,6,12 (Jan, Jun, Dec)",
                week: "Allowed values: 0-7 (0 and 7 are Sunday), 1-5 (Mon to Fri), */2 (every other day)",
            },
        };
    },
    watch: {
        timeUnits: {
            handler() {
                this.updateNextRunTimes();
            },
            deep: true,
        },
    },

    methods: {
        reset() {
            this.email = this.$currentUser.email;
            this.selectedSchedule = "weekly";
            this.name = this.$router.currentRoute.path
                .replace('/', '')
                .replaceAll('/', ' ')
                .replaceAll('-', ' ')
                .replaceAll('/reports/', 'report - &nbsp;');

            // set each word capital letter
            this.name = this.name.replace(/\w\S*/g, function (txt) {
                return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
            });
            this.name = this.name + " - ";
            this.timeUnits.hour = "6";

            this.updateNextRunTimes();
        },

        setSchedule(schedule) {
            const schedules = {
                hourly: { minute: "0", hour: "*", day: "*", month: "*", week: "*" },
                daily: { minute: "0", hour: "6", day: "*", month: "*", week: "*" },
                weekly: { minute: "0", hour: "6", day: "*", month: "*", week: "MON" },
                monthly: { minute: "0", hour: "6", day: "1", month: "*", week: "*" },
                yearly: { minute: "0", hour: "6", day: "1", month: "1", week: "*" },
            };

            this.selectedSchedule = schedule;

            this.timeUnits = schedules[schedule];
        },

        updateNextRunTimes() {
            const cronExpression = `${this.timeUnits.minute} ${this.timeUnits.hour} ${this.timeUnits.day} ${this.timeUnits.month} ${this.timeUnits.week}`;
            try {
                const interval = parseExpression(cronExpression);
                const nextDates = Array.from({ length: 1 }, () => interval.next().toDate());
                const humanReadable = cronstrue.toString(cronExpression);
                this.nextRunTimes = nextDates.map(
                (date, index) =>
                    `${humanReadable}`
                );
            } catch {
                this.nextRunTimes = ["Invalid cron expression"];
            }
        },

        async createScheduledReport() {
            const isValid = await this.$refs.createScheduleReportForm.validate();
            if (isValid) {
                this.apiPostScheduledReport({
                    name: this.name,
                    email: this.email,
                    uri: this.$route.fullPath,
                    cron: `${this.timeUnits.minute} ${this.timeUnits.hour} ${this.timeUnits.day} ${this.timeUnits.month} ${this.timeUnits.week}`,
                }).then((response) => {
                    this.notifySuccess('Report has been scheduled');
                    this.$bvModal.hide('scheduled-report-create-modal');
                }).catch((error) => {
                    this.displayApiCallError(error);
                });
            }
        }
    },
};
</script>

<style>
/* Add your styling here */
</style>
