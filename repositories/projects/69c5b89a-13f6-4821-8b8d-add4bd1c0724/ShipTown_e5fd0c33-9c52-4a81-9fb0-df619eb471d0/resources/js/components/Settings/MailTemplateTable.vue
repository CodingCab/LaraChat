<template>
    <div>
        <div class="card card-default">
            <div class="card-header">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span>
                        {{ $t('Mail Templates') }}
                    </span>
                    <button class="btn btn-sm btn-primary" @click="showCreateModal">{{ $t('New') }}</button>
                </div>
            </div>

            <div class="card-body">
                <table v-if="mailTemplates.length > 0" class="table table-borderless table-responsive mb-0">
                    <tbody>
                        <tr v-for="(mailTemplate, i) in mailTemplates" :key="i">
                            <td>
                                <a :href='"mail-templates/" + mailTemplate.id + "/preview"' target="_blank" :id="`preview-link-${mailTemplate.id}`">
                                    {{ mailTemplate.name }}
                                </a>
                            </td>
                            <td>
                                <a @click.prevent="showEditForm(mailTemplate)" :id="`edit-${mailTemplate.id}`">
                                    <font-awesome-icon icon="edit"></font-awesome-icon>
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <p v-else class="mb-0">
                    {{ $t('No mail template found.') }}
                </p>

            </div>
        </div>

        variables.order.id<br>
        variables.order.order_number<br>
        variables.order.status_code<br>
        variables.order.recount_required<br>
        variables.order.label_template<br>
        variables.order.is_active<br>
        variables.order.is_on_hold<br>
        variables.order.is_editing<br>
        variables.order.is_fully_paid<br>
        variables.order.product_line_count<br>
        variables.order.total_products<br>
        variables.order.total_shipping<br>
        variables.order.total_discounts<br>
        variables.order.total_order<br>
        variables.order.total_paid<br>
        variables.order.total_outstanding<br>
        variables.order.shipping_address_id<br>
        variables.order.billing_address_id<br>
        variables.order.shipping_method_code<br>
        variables.order.shipping_method_name<br>
        variables.order.packer_user_id<br>
        variables.order.order_placed_at<br>
        variables.order.picked_at<br>
        variables.order.packed_at<br>
        variables.order.order_closed_at<br>
        variables.order.deleted_at<br>
        variables.order.created_at<br>
        variables.order.updated_at<br>
        variables.order.custom_unique_reference_id<br>
        variables.order.is_picked<br>
        variables.order.is_packed<br>
        variables.order.age_in_days<br>
        variables.order.order_shipments<br>
        variables.order.order_products<br>
        variables.order.shipping_address.id<br>
        variables.order.shipping_address.company<br>
        variables.order.shipping_address.gender<br>
        variables.order.shipping_address.address1<br>
        variables.order.shipping_address.address2<br>
        variables.order.shipping_address.postcode<br>
        variables.order.shipping_address.city<br>
        variables.order.shipping_address.state_code<br>
        variables.order.shipping_address.state_name<br>
        variables.order.shipping_address.country_code<br>
        variables.order.shipping_address.country_name<br>
        variables.order.shipping_address.fax<br>
        variables.order.shipping_address.website<br>
        variables.order.shipping_address.discount_code<br>
        variables.order.shipping_address.region<br>
        variables.order.shipping_address.document_type<br>
        variables.order.shipping_address.tax_id_encrypted<br>
        variables.order.shipping_address.tax_id_first_3_chars_md5<br>
        variables.order.shipping_address.tax_exempt<br>
        variables.order.shipping_address.created_at<br>
        variables.order.shipping_address.updated_at<br>
        variables.order.shipping_address.deleted_at<br>
        variables.order.shipping_address.first_name<br>
        variables.order.shipping_address.last_name<br>
        variables.order.shipping_address.phone<br>
        variables.order.shipping_address.email<br>
        variables.order.shipping_address.document_number<br>
        variables.order.shipping_address.tax_id<br>
        variables.order.billing_address.id<br>
        variables.order.billing_address.company<br>
        variables.order.billing_address.gender<br>
        variables.order.billing_address.address1<br>
        variables.order.billing_address.address2<br>
        variables.order.billing_address.postcode<br>
        variables.order.billing_address.city<br>
        variables.order.billing_address.state_code<br>
        variables.order.billing_address.state_name<br>
        variables.order.billing_address.country_code<br>
        variables.order.billing_address.country_name<br>
        variables.order.billing_address.fax<br>
        variables.order.billing_address.website<br>
        variables.order.billing_address.discount_code<br>
        variables.order.billing_address.region<br>
        variables.order.billing_address.document_type<br>
        variables.order.billing_address.tax_id_encrypted<br>
        variables.order.billing_address.tax_id_first_3_chars_md5<br>
        variables.order.billing_address.tax_exempt<br>
        variables.order.billing_address.created_at<br>
        variables.order.billing_address.updated_at<br>
        variables.order.billing_address.deleted_at<br>
        variables.order.billing_address.first_name<br>
        variables.order.billing_address.last_name<br>
        variables.order.billing_address.phone<br>
        variables.order.billing_address.email<br>
        variables.order.billing_address.document_number<br>
        variables.order.billing_address.tax_id<br>
        variables.shipments<br>
        variables.not_packed_products<br>

        <!-- The modals -->
        <edit-modal :mailTemplate="selectedMailTemplate" id="editForm" @onUpdated="updateMailTemplate"></edit-modal>
    </div>
</template>

<script>

import EditModal from './MailTemplate/EditModal';
import api from "../../mixins/api.vue";
import Modals from '../../plugins/Modals';

export default {
    mixins: [api],
    components: {
        'edit-modal': EditModal,
    },

    mounted() {
        this.loadMailTemplates();

        Modals.EventBus.$on('hide::modal::create-mail-template-modal', (data) => {
            if (typeof data.refreshList !== 'undefined' && data.refreshList) {
                this.loadMailTemplates();
            }
        });
    },

    data: () => ({
        mailTemplates: [],
        selectedMailTemplate: {}
    }),

    methods: {
        showEditForm(mailTemplate) {
            this.selectedMailTemplate = mailTemplate;
            $('#editForm').modal('show');
        },
        updateMailTemplate(newValue) {
            const indexMailTemplate = this.mailTemplates.findIndex(mailTemplate => mailTemplate.id == newValue.id)
            this.$set(this.mailTemplates, indexMailTemplate, newValue)
        },
        showCreateModal() {
            this.$modal.showCreateMailTemplateModal();
        },
        loadMailTemplates() {
            this.apiGetMailTemplate()
                .then(({ data }) => {
                    this.mailTemplates = data.data;
                })
        }
    },
}
</script>
