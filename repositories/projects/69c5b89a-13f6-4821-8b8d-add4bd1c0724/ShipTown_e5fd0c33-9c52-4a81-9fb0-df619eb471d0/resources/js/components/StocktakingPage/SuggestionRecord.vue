<template>
    <div>
        <div class="row">
            <div class="col-sm-12 col-md-4">
                <product-info-card :product= "record['product']"></product-info-card>
            </div>
            <div class="col-sm-12 col-md-4 text-left small text-nowrap cursor-pointer" @click.prevent="showInventoryMovementModal">
                <div class="small" :class="{ 'bg-warning':  Number(record['inventory']['quantity_available']) < 0}">{{ $t('in stock') }}: <strong>{{ dashIfZero(Number(record['inventory']['quantity_available'])) }}</strong></div>
                <div class="small">{{ $t('price') }}: <strong>{{ Number(productPrice) }}</strong></div>
                <div class="small">{{ $t('in stock since') }}: <span class="link">{{ formatDateTime(record['inventory']['in_stock_since']) }}</span></div>
                <div class="small">{{ $t('last sold at') }}: <span class="link">{{ formatDateTime(record['inventory']['last_sold_at']) }}</span></div>
                <div class="small">{{ $t('last counted at') }}: <span class="link">{{ formatDateTime(record['inventory']['last_counted_at']) }}</span></div>
                <div>
                    <div class="small d-inline">{{ $t('last movement at') }}: <span class="link">{{ formatDateTime(record['inventory']['last_movement_at']) }}</span></div>
                </div>

                <template v-if="expanded">
                    <div class="row mb-3">
                        <div class="small col-12">{{ $t('last received at') }}: <span class="link">{{ formatDateTime(record['inventory']['last_received_at']) }}</span></div>
                        <div class="small col-12">{{ $t('first received at') }}: <span class="link">{{ formatDateTime(record['inventory']['first_received_at']) }}</span></div>
                    </div>
                </template>

            </div>

            <div @click="toggleDetails" class="col-sm-12 col-md-4 text-right cursor-pointer">
                <div class="row">
                    <div class="col">
                        <text-card :label="$t('warehouse')" :text="record['inventory']['warehouse_code']" class="fa-pull-left" ></text-card>
                        <number-card :label="$t('points')" :number="record['points']"></number-card>
                        <text-card :label="$t('shelf')" :text="record['inventory']['shelve_location']"></text-card>
                    </div>
                </div>

                <div class="row">
                    <div class="col text-left small">
                        {{ record['reason'] }}
                    </div>
                </div>
                <div class="row">
                    <div class="col text-center">
                        <font-awesome-icon v-if="!expanded" icon="chevron-down" class="fa fa-xs"></font-awesome-icon>
                        <font-awesome-icon v-if="expanded" icon="chevron-up" class="fa fa-xs text-secondary"></font-awesome-icon>
                    </div>
                </div>

            </div>
        </div>

    </div>
</template>

<script>
import loadingOverlay from '../../mixins/loading-overlay';
import BarcodeInputField from "./../SharedComponents/BarcodeInputField";
import api from "../../mixins/api";
import helpers from "../../mixins/helpers";
import url from "../../mixins/url";

export default {
      name: 'SuggestionRecord',

      mixins: [loadingOverlay, url, api, helpers],

      components: {
          BarcodeInputField
      },

      props: {
          record: {
              type: Object,
              required: true
          }
      },

      data: function() {
          return {
              expanded: false,
              suggestionDetails: null,
              showMovementSku: null
          };
      },

      computed: {
          productPrice: function() {
            return this.record['product']['prices'][this.record['inventory']['warehouse_code']]['price'];
          }
      },


      methods: {
          toggleDetails() {
            this.expanded = !this.expanded;
          },

          showInventoryMovementModal() {
              this.$modal.showRecentInventoryMovementsModal(this.record['inventory_id']);
          }
      },
  }
</script>
<style lang="scss" scoped>
</style>
