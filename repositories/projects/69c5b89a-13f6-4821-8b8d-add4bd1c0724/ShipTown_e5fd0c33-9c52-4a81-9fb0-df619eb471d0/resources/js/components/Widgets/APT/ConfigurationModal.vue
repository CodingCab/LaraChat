<template>
    <div class="modal fade widget-configuration-modal" tabindex="-1" role="dialog" aria-labelledby="aptConfigurationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div ref="loadingContainer" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $t('30 DAY APT') }}</h5>
                    <div class="widget-tools-container">
                        <font-awesome-icon icon="question-circle" :content="helpText" v-tippy></font-awesome-icon>
                    </div>
                </div>
                <div class="modal-body" style="margin: 0 auto 0;">
                    <form method="POST" action="gago" @submit.prevent="handleSubmit">
                        <div v-for="(status,i) in statuses" class="form-group form-check" :key="i">
                            <input v-model="config[status]" :name="status" type="checkbox" class="form-check-input" :id="`cb-${status}`" />
                            <label class="form-check-label" :for="`cb-${status}`">{{ status }}</label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ $t('Cancel') }}</button>
                    <button type="button" @click.prevent="handleSubmit" class="btn btn-primary">{{ $t('Save') }}</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import loadingOverlay from '../../../mixins/loading-overlay';
import api from "../../../mixins/api";

export default {
    mixins: [api, loadingOverlay],

    created() {
        if (this.widgetId) {
            this.id = this.widgetId;
        }

        this.config = Object.assign({}, this.config, this.widgetConfig);
    },

    props: {
        statuses: Array,
        widgetConfig: [Array, Object],
        widgetId: [Number, String],
        name: String,
    },

    data: () => ({
        id: null,
        config: {}
    }),

    computed: {
        helpText() {
            let text = 'APT - Average Processing Time<br>' +
                        '<br>' +
                        'This is the average time difference between time when order has been placed' +
                        'and time when status was first changed to something different that "processsing" <br>' +
                        '<br>' +
                        'Only orders with one of the following statuses are taken into calculations:<br><ul>';

            this.statuses.forEach(status => {
                text += `<li>${status}</li>`;
            });

            text += '</ul>';
            return text;
        }
    },

    methods: {
        handleSubmit() {
            const data = {
                name: this.name,
                config: this.config
            };

            if (this.id) {
                this.apiPutWidget(this.id, data);
            }

            this.showLoading();

            this.apiPostWidget(data)
                .then(({ data }) => {
                    const widget = data.data;
                    this.id = widget.id;
                    this.config = widget.config;

                    this.$snotify.success('APT Widget configuration saved.');
                    $(this.$el).modal('hide');
                    window.location.reload();
                })
                .finally(this.hideLoading);
        }
    }
}
</script>
