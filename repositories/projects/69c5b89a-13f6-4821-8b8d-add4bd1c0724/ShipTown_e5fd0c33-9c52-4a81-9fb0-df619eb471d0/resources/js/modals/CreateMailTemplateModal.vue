<template>
    <b-modal :title="$t('New Mail Template')" :id="modalId" no-fade no-close-on-backdrop size="xl">
        <ValidationObserver ref="form">
            <form class="form" @submit.prevent="submit" ref="loadingContainer">

<!--                <div class="form-group">-->
<!--                    <label class="form-label" for="mailable">{{ $t('Mailable') }}</label>-->
<!--                    <ValidationProvider vid="mailable" name="mailable" v-slot="{ errors }">-->
<!--                        <input type="text"-->
<!--                                id="create-mailable"-->
<!--                                class="form-control"-->
<!--                                :class="{'is-invalid' : errors.length}"-->
<!--                                v-model="mailable"-->
<!--                                required>-->
<!--                        <div class="invalid-feedback">-->
<!--                            {{ errors[0] }}-->
<!--                        </div>-->
<!--                    </ValidationProvider>-->
<!--                </div>-->

                <div class="form-group">
                    <label class="form-label" for="code">{{ $t('Name') }}</label>
                    <ValidationProvider vid="code" name="code" v-slot="{ errors }">
                        <input type="text"
                                id="create-code"
                                class="form-control"
                                :class="{'is-invalid' : errors.length}"
                                v-model="code"
                                required>
                        <div class="invalid-feedback">
                            {{ errors[0] }}
                        </div>
                    </ValidationProvider>
                </div>

                <div class="form-group">
                    <label class="form-label" for="sender_name">Sender Name</label>
                    <ValidationProvider vid="sender_name" name="sender_name" v-slot="{ errors }">
                        <input type="text"
                                id="create-sender-name"
                                class="form-control"
                                :class="{'is-invalid' : errors.length}"
                                v-model="sender_name"
                                >
                        <div class="invalid-feedback">
                            {{ errors[0] }}
                        </div>
                    </ValidationProvider>
                </div>

                <div class="form-group">
                    <label class="form-label" for="sender_email">Sender Email</label>
                    <ValidationProvider vid="sender_email" name="sender_email" v-slot="{ errors }">
                        <input type="text"
                                id="create-sender-email"
                                class="form-control"
                                :class="{'is-invalid' : errors.length}"
                                v-model="sender_email"
                                >
                        <div class="invalid-feedback">
                            {{ errors[0] }}
                        </div>
                    </ValidationProvider>
                </div>

                <div class="form-group">
                    <label class="form-label" for="to">To</label>
                    <ValidationProvider vid="to" name="to" v-slot="{ errors }">
                        <input type="text"
                            id="create-to"
                            class="form-control"
                            :class="{'is-invalid' : errors.length}"
                            v-model="to"
                            >
                        <div class="invalid-feedback">
                            {{ errors[0] }}
                        </div>
                    </ValidationProvider>
                </div>

                <div class="form-group">
                    <label class="form-label" for="reply_to">{{ $t('Reply To') }}</label>
                    <ValidationProvider vid="reply_to" name="reply_to" v-slot="{ errors }">
                        <input type="text"
                                id="create-reply_to"
                                class="form-control"
                                :class="{'is-invalid' : errors.length}"
                                v-model="replyTo"
                                >
                        <div class="invalid-feedback">
                            {{ errors[0] }}
                        </div>
                    </ValidationProvider>
                </div>

                <div class="form-group">
                    <label class="form-label" for="subject">{{ $t('Subject') }}</label>
                    <ValidationProvider vid="subject" name="subject" v-slot="{ errors }">
                        <input type="text"
                                id="create-subject"
                                class="form-control"
                                :class="{'is-invalid' : errors.length}"
                                v-model="subject"
                                required>
                        <div class="invalid-feedback">
                            {{ errors[0] }}
                        </div>
                    </ValidationProvider>
                </div>

                <div class="form-group">
                    <label class="form-label" for="html_template">{{ $t('Html Template') }}</label>
                    <ValidationProvider vid="html_template" name="html_template" v-slot="{ errors }">
                        <textarea
                            id="create-html_template"
                            class="form-control"
                            rows="5"
                            :class="{'is-invalid' : errors.length}"
                            v-model="htmlTemplate"
                            ></textarea>
                        <div class="invalid-feedback">
                            {{ errors[0] }}
                        </div>
                    </ValidationProvider>
                </div>

                <div class="form-group">
                    <label class="form-label" for="text_template">{{ $t('Text Template') }}</label>
                    <ValidationProvider vid="text_template" name="text_template" v-slot="{ errors }">
                        <textarea
                            id="create-text_template"
                            class="form-control"
                            rows="5"
                            :class="{'is-invalid' : errors.length}"
                            v-model="textTemplate"
                            ></textarea>
                        <div class="invalid-feedback">
                            {{ errors[0] }}
                        </div>
                    </ValidationProvider>
                </div>

            </form>
        </ValidationObserver>

        <template #modal-footer>
            <b-button variant="secondary" class="float-right" @click="closeModal" dusk="btn-cancel" :disabled="isLoading">
                {{ $t('Cancel') }}
            </b-button>
            <b-button variant="primary" class="float-right" @click="saveMailTemplate" dusk="btn-submit" :disabled="isLoading">
                {{ $t('OK') }}
            </b-button>
        </template>
    </b-modal>
</template>

<script>
import { ValidationObserver, ValidationProvider } from "vee-validate";

import loadingOverlay from '../mixins/loading-overlay';
import api from "../mixins/api";
import helpers from "../helpers";
import Modals from '../plugins/Modals';

export default {
    name: "CreateMailTemplateModal",
    mixins: [loadingOverlay, api, helpers],
    components: {ValidationObserver, ValidationProvider},
    data() {
        return {
            modalId: 'create-mail-template-modal',
            subject: "",
            replyTo: "",
            to: "",
            sender_name: "",
            sender_email: "",
            htmlTemplate: "",
            textTemplate: "",
            mailable: "",
            code: "",
        }
    },

    beforeMount() {
        Modals.EventBus.$on('show::modal::' + this.modalId, () => {
            this.$bvModal.show(this.modalId);
        })
    },

    mounted() {

    },
    methods: {
        closeModal() {
            this.$emit('closeModal');
            this.$bvModal.hide(this.modalId);
        },

        resetData() {
            this.subject = ""
            this.replyTo = ""
            this.to = ""
            this.sender_name = ""
            this.sender_email = ""
            this.htmlTemplate = ""
            this.textTemplate = ""
            this.mailable = ""
            this.code = ""
        },

        async saveMailTemplate() {
            this.showLoading();
            const isValid = await this.$refs.form.validate();
            if (isValid) {
                this.apiStoreMailTemplate({
                    sender_name: this.sender_name,
                    sender_email: this.sender_email,
                    subject: this.subject,
                    reply_to: this.replyTo,
                    to: this.to,
                    html_template: this.htmlTemplate,
                    text_template: this.textTemplate,
                    mailable: 'App\\Mail\\OrderMail',
                    code: this.code,
                })
                .then(({ data }) => {
                    Modals.EventBus.$emit('hide::modal::' + this.modalId, {refreshList: true});
                    this.notifySuccess(this.$t('Mail template created'), false)
                    this.resetData()
                    this.closeModal()
                })
                .catch((e) => {
                    const response = e.response;

                    if (response) {
                        if (response.status === 422) {
                            this.$refs.form.setErrors(response.data.errors);
                        } else {
                            this.displayApiCallError(e);
                        }
                    }
                })
                .finally(() => {
                    this.hideLoading();
                });
            }
        }
    }
}
</script>
