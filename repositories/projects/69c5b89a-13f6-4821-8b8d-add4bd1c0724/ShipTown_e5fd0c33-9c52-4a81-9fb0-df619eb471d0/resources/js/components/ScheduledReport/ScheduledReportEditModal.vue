<template>
    <b-modal :title="$t('Edit Scheduled Report')" id="scheduled-report-edit-modal" no-fade>
        <ValidationObserver ref="updateScheduleReportForm">
            <form class="form" ref="loadingContainer" @submit.prevent="submit">
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label" for="name">{{ $t('Report Name') }}</label>
                    <div class="col-sm-9">
                        <ValidationProvider vid="name" name="name" v-slot="{ errors }">
                            <input v-model="name" :class="{
                                'form-control': true,
                                'is-invalid': errors.length > 0,
                            }" id="schedule-report-modal-name-input" dusk="input-name" required>
                            <div class="invalid-feedback">{{ errors[0] }}</div>
                        </ValidationProvider>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-form-label" for="scheduled-email">{{ $t('Email') }}</label>
                    <div class="col-sm-9">
                            <input v-model="email" class="form-control" id="schedule-report-modal-email-input" type="email" dusk="input-email" required>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-form-label" for="scheduled-email"></label>
                    <div class="col-sm-9">
                        <a :href="uri" class="btn btn-outline-primary" target="_blank">{{ $t('PREVIEW') }}</a>
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
            <b-button variant="danger" class="float-right" @click="deleteScheduledReport" dusk="btn-delete">
                {{ $t('Delete')}}
            </b-button>
            <b-button variant="secondary" class="float-right" @click="$bvModal.hide('scheduled-report-edit-modal');" dusk="btn-cancel">
                {{ $t('Cancel') }}
            </b-button>
            <b-button variant="primary" class="float-right" @click="updateScheduledReport" dusk="btn-submit">
                {{ $t('OK') }}
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

export default {
    name: "ScheduledReportEditModal",
    components: {
        ValidationObserver,
        ValidationProvider,
    },
    mixins: [loadingOverlay, api, helpers],
    props: {
        scheduledReport: {
            type: Object|null,
            required: true,
        },
    },
    data() {
        return {
            name: "",
            email: "",
            uri: "",
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
        scheduledReport: {
            handler() {
                this.name = this.scheduledReport.name;
                this.email = this.scheduledReport.email;
                this.uri = this.scheduledReport.uri;
                const arrCron = this.scheduledReport.cron.split(" ");
                this.timeUnits = {
                    minute: arrCron[0],
                    hour: arrCron[1],
                    day: arrCron[2],
                    month: arrCron[3],
                    week: arrCron[4],
                };
            },
            deep: true,
        },
    },
    methods: {
        setSchedule(schedule) {
            const schedules = {
                hourly: { minute: "0", hour: "*", day: "*", month: "*", week: "*" },
                daily: { minute: "0", hour: "0", day: "*", month: "*", week: "*" },
                weekly: { minute: "0", hour: "0", day: "*", month: "*", week: "0" },
                monthly: { minute: "0", hour: "0", day: "1", month: "*", week: "*" },
                yearly: { minute: "0", hour: "0", day: "1", month: "1", week: "*" },
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
        async updateScheduledReport() {
            const isValid = await this.$refs.updateScheduleReportForm.validate();
            if (isValid) {
                this.apiPutScheduledReport(this.scheduledReport.id, {
                    name: this.name,
                    email: this.email,
                    cron: `${this.timeUnits.minute} ${this.timeUnits.hour} ${this.timeUnits.day} ${this.timeUnits.month} ${this.timeUnits.week}`,
                }).then((response) => {
                    this.notifySuccess('Report has been saved');
                    this.$bvModal.hide('scheduled-report-edit-modal');
                    this.$emit('updated');
                }).catch((error) => {
                    this.displayApiCallError(error);
                    if (error.response && error.response.status === 422) {
                        this.$refs.updateScheduleReportForm.setErrors(error.response.data.errors);
                    } else {
                        this.notifyError(msg);
                    }
                });
            }
        },

        deleteScheduledReport() {
            this.$snotify.confirm(this.$t('Once deleted, data cannot be restored'), this.$t('Are you sure?'), {
                    position: 'centerCenter',
                    buttons: [
                        {
                            text: 'Yes',
                            action: (toast) => {
                                this.apiDeleteScheduledReport(this.scheduledReport.id).then((response) => {
                                    this.$bvModal.hide('scheduled-report-edit-modal');
                                    this.$emit('updated');
                                }).catch((error) => {
                                    this.notifyError(msg);
                                });
                                this.$snotify.remove(toast.id);
                            }
                        },
                        { text: 'Cancel' },
                    ]
                });
        }
    },
};
</script>

<style>
/* Add your styling here */
</style>
