<template>
    <div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" >
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div ref="loadingContainer2" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $t('Edit Mail Template') }}</h5>
                </div>
                <div class="modal-body">
                    <ValidationObserver ref="form">
                        <form class="form" @submit.prevent="submit" ref="loadingContainer">
                            <div class="form-group">
                                <label class="form-label" for="to">Sender Name</label>
                                <ValidationProvider vid="sender_name" name="sender_name" v-slot="{ errors }">
                                    <input type="text"
                                        id="edit-sender-name"
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
                                <label class="form-label" for="to">Sender Email</label>
                                <ValidationProvider vid="sender_email" name="sender_email" v-slot="{ errors }">
                                    <input type="text"
                                        id="edit-sender-email"
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
                                <label class="form-label" for="reply_to">{{ $t('Reply To') }}</label>
                                <ValidationProvider vid="reply_to" name="reply_to" v-slot="{ errors }">
                                    <input type="text"
                                           id="edit-reply_to"
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
                                <label class="form-label" for="to">To</label>
                                <ValidationProvider vid="to" name="to" v-slot="{ errors }">
                                    <input type="text"
                                        id="edit-to"
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
                                <label class="form-label" for="subject">{{ $t('Subject') }}</label>
                                <ValidationProvider vid="subject" name="subject" v-slot="{ errors }">
                                    <input type="text"
                                           id="edit-subject"
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
                                        id="edit-html_template"
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
                                        id="edit-text_template"
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
                </div>
                <div class="modal-footer">
                    <button type="button" id="edit-save" @click="closeModal" class="btn btn-secondary">{{ $t('Cancel') }}</button>
                    <button type="button" @click="submit" class="btn btn-primary">{{ $t('Save') }}</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ValidationObserver, ValidationProvider } from "vee-validate";

import Loading from "../../../mixins/loading-overlay";
import api from "../../../mixins/api";

export default {
    name: "EditModal",

    mixins: [api, Loading],

    components: {
        ValidationObserver, ValidationProvider
    },

    data() {
        return {
            sender_name: "",
            sender_email: "",
            subject: "",
            replyTo: "",
            to: "",
            htmlTemplate: "",
            textTemplate: "",
        }
    },

    props: {
        mailTemplate: Object,
    },

    watch: {
        mailTemplate: function(newVal) {
            this.sender_name = newVal.sender_name;
            this.sender_email = newVal.sender_email;
            this.subject = newVal.subject;
            this.replyTo = newVal.reply_to;
            this.to = newVal.to;
            this.htmlTemplate = newVal.html_template;
            this.textTemplate = newVal.text_template;
        }
    },

    methods: {

        submit() {
            this.showLoading();
            this.apiPutMailTemplate(this.mailTemplate.id, {
                    sender_name: this.sender_name,
                    sender_email: this.sender_email,
                    subject: this.subject,
                    reply_to: this.replyTo,
                    to: this.to,
                    html_template: this.htmlTemplate,
                    text_template: this.textTemplate,
                })
                .then(({ data }) => {
                    this.$snotify.success('Mail template updated.');
                    this.closeModal();
                    this.$emit('onUpdated', data.data);
                })
                .catch((error) => {
                    let response = error.response;

                    if (response) {
                        if (response.status === 422) {
                          this.$refs.form.setErrors(response.data.errors);
                        } else {
                          this.$snotify.error('Error saving template: ' + response.status + ' ' + response.data.message);
                        }
                    }
                })
                .finally(this.hideLoading);
        },

        closeModal() {
            $(this.$el).modal('hide');
        }
    },
}
</script>
