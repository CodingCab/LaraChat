<template>
    <div>
        <report report-url="/api/modules/autostatus/picking/configuration" @click="editRecord">
            <template #report-header>
                {{ $t('Automation - Order Status Refilling') }}
            </template>
            <template #buttons>
                <top-nav-button icon="plus" buttonId="add-button" v-b-modal="'add-batch-automation'"/>
            </template>
        </report>

        <options-modal :id="'add-batch-automation'" :title='$t("Create Order Batch Automation")' size="md" :show-stocktake-input="false" @hidden="resetModal">
                <b-form class="mb-3">
                    <b-input :placeholder="$t('From Status Code')" v-model="automationCurrentlyEditing['from_status_code']" class="mt-2"></b-input>
                    <b-input :placeholder="$t('To Status Code')" v-model="automationCurrentlyEditing['to_status_code']" class="mt-2"></b-input>
                    <b-input :placeholder="$t('Desired Order Count')" v-model="automationCurrentlyEditing['desired_order_count']" type="number" class="mt-2"></b-input>
                    <b-checkbox v-model="refill_only_at_0" :true-value="1" :false-value="0" class="mt-2">{{ $t('Refill at 0') }}</b-checkbox>
                </b-form>
            <column-wrapped>
                <template #left-text>
                    <b-btn variant="outline-danger" @click="deleteRecord" :disabled="disableButtons">{{ $t('Delete') }}</b-btn>
                </template>
                <template #right-text>
                    <b-btn :disabled="disableButtons" @click="$bvModal.hide('add-batch-automation')">{{ $t('Cancel') }}</b-btn>
                    <b-btn :disabled="disableButtons" variant="primary" @click="updateConfig">{{ $t('Save') }}</b-btn>
                </template>
            </column-wrapped>
        </options-modal>
    </div>
</template>

<script>
import api from "../../../mixins/api";
import Toggle from "./Toggle.vue";
import OptionsModal from "../../OptionsModal.vue";
import url from "../../../mixins/url";
import ColumnWrapped from "../../Orders/ColumnWrapped.vue";

export default {
    components: {ColumnWrapped, OptionsModal, Toggle},
    mixins: [api, url],

    name: "AutoPickingRefillingPage",

    data: function () {
        return {
            disableButtons: false,
            automationCurrentlyEditing: {},
            configurationsList: {},
        }
    },

    computed: {
        'refill_only_at_0': {
            get() {
                return this.automationCurrentlyEditing['refill_only_at_0'] === 1;
            },
            set(value) {
                this.automationCurrentlyEditing['refill_only_at_0'] = value ? 1 : 0;
            }
        }
    },

    methods: {
        deleteRecord() {
           this.$snotify.confirm(this.$t('Are you sure you want to delete this automation?'), {
                title: this.$t('Delete Automation'),
                position: 'centerCenter',
                body: this.$t('This action cannot be undone.'),
                timeout: 0,
                showCancelButton: true,
                cancelButtonText: this.$t('Cancel'),
                confirmButtonText: this.$t('Delete'),
                type: 'danger',
                buttons: [
                    {
                        text: this.$t('Cancel'),
                        action: (toast) => {
                            this.$snotify.remove(toast.id);
                        },
                        bold: true,
                    },
                    {
                        text: this.$t('Delete'),
                        action: (toast) => {
                            this.disableButtons = true;
                            this.$snotify.remove(toast.id);
                            this.$bvModal.hide('add-batch-automation');
                            this.apiDeleteModuleAutoStatusPickingConfiguration(this.automationCurrentlyEditing)
                                .then(() => {
                                    this.notifySuccess(this.$t('Automation deleted successfully'));
                                    this.resetModal();
                                })
                                .catch((error) => {
                                    this.displayApiCallError(error);
                                })
                                .finally(() => {
                                    this.disableButtons = false;
                                });
                        },
                        bold: true,
                    }
                ]
            });


        },

        resetModal() {
            this.disableButtons = false;
            this.automationCurrentlyEditing = {};
            this.setUrlParameter('t',new Date().getTime(), true);
        },

        editRecord(record, fieldName) {
            this.automationCurrentlyEditing = {...record};
            this.$bvModal.show('add-batch-automation');
        },

        updateConfig() {
            this.apiSetModuleAutoStatusPickingConfiguration(this.automationCurrentlyEditing)
                .then(() => {
                    this.$bvModal.hide('add-batch-automation');
                    this.notifySuccess(this.$t('Automation created successfully'));
                    this.automationCurrentlyEditing = {};
                })
                .catch((error) => {
                    this.displayApiCallError(error);
                })
                .finally(() => {
                    this.disableButtons = false;
                });
        },
    }
}
</script>
