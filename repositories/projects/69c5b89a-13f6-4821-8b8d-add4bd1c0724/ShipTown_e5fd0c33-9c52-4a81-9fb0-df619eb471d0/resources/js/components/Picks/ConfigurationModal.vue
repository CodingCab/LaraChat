<template>
    <div class="modal fade widget-configuration-modal" tabindex="-1" role="dialog" aria-labelledby="picklistConfigurationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div ref="loadingContainer" class="modal-content">
                <div class="modal-header">
                  <stocktake-input></stocktake-input>
                </div>
                <div class="modal-body" style="margin: 0 auto 0;">
                    <form method="POST" @submit.prevent="handleSubmit">
                        <div class="form-group form-check">
                            <input v-model="urlFilters['in_stock_only']" type="checkbox" class="form-check-input" />
                            <label class="form-check-label" >{{ $t('In stock only') }}</label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" @click.prevent="handleSubmit" class="btn btn-primary">{{ $t('OK') }}</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import url from "../../mixins/url";
import Vue from "vue";

export default {

    mixins: [url],

    props: {
        picklistFilters: Object,
    },

    data() {
        return {
            urlFilters: {
                'inventory_source_location_id':
                    this.getUrlParameter('inventory_source_location_id', 100),

                'in_stock_only':
                    this.getUrlFilter('in_stock_only', true)
            }
        }
    },

    mounted() {
        this.setUrlParameter('warehouse_id', Vue.prototype.$currentUser['warehouse_id']);
    },

    computed: {
        helpText() {
            return this.$t('Single line orders are orders with only single product ordered.');
        }
    },

    methods: {
        handleSubmit() {
            for (let urlFilter in this.urlFilters) {
                this.setUrlParameter(urlFilter, this.urlFilters[urlFilter])
            }
            this.$emit('btnSaveClicked');
            $(this.$el).modal('hide');
        }
    }
}
</script>
