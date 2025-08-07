<template>
  <div class="card mb-2">
    <div class="row card-body p-1 p-md-2 h-100 px-3" v-if="record !== null">
        <div class="col-12 col-lg-4 align-text-top pt-2">
            <product-info-card :product= "record['product']" :show-tags="false" :show-product-descriptions="false"></product-info-card>
        </div>
        <div class="col-12 col-lg-4 small pr-lg-5">
            <column-wrapped :left-text="$t('occurred at')" :right-text="formatDateTime(record['occurred_at'])"></column-wrapped>
            <column-wrapped :left-text="$t('type')">
                <template #right-text>
                    <div :class="record['type'] === 'stocktake' ? 'bg-warning' : ''">
                        <a :href="getLinkInvetoryMovementsReport('filter[type]', record['type'])">{{ record['type'] }}</a>
                    </div>
                </template>
            </column-wrapped>
<!--                description -->
            <column-wrapped :left-text="$t('description')">
                <template #right-text>
                    <a :href="getLinkInvetoryMovementsReport('filter[description]', record['description'])">{{ record['description'] }}</a>
                </template>
            </column-wrapped>
            <column-wrapped :left-text="$t('by')">
                <template #right-text>
                    {{ record['user'] ? record['user']['name'] : '' }}
                </template>
            </column-wrapped>
        </div>

        <div class="col-12 col-lg-4 text-right align-text-top" @click="toggleDetails()">
            <div class="row m-0 text-right">
                <div class="col m-0 nowrap text-nowrap">
                    <text-card :label="$t('warehouse')" :text="record['warehouse_code']" class="fa-pull-left"></text-card>
                    <number-card :label="$t('before')" :number="record['quantity_before']"></number-card>
                    <number-card :label="$t('change')" :number="record['quantity_delta']"></number-card>
                    <number-card :label="$t('after')" :number="record['quantity_after']"></number-card>
                </div>
            </div>
            <div class="row text-center">
                <div class="col">
                    <font-awesome-icon v-if="showDetails" icon="chevron-up" class="fa fa-xs"></font-awesome-icon>
                    <font-awesome-icon v-if="!showDetails" icon="chevron-down" class="fa fa-xs"></font-awesome-icon>
                </div>
            </div>

            <template v-if="showDetails">
                <column-wrapped>
                    <template #left-text @click="toggleDetails()">{{ $t('movement id') }}:</template>
                    <template #right-text class="pl-1">{{ record['id'] }}</template>
                </column-wrapped>
                <column-wrapped>
                    <template #left-text @click="toggleDetails()">{{ $t('sequence number') }}:</template>
                    <template #right-text  class="pl-1">{{ record['sequence_number'] }}</template>
                </column-wrapped>
                <column-wrapped>
                    <template #left-text @click="toggleDetails()">{{ $t('uuid') }}:</template>
                    <template #right-text  class="pl-1">{{ record['custom_unique_reference_id'] }}</template>
                </column-wrapped>
                <column-wrapped>
                    <template #left-text @click="toggleDetails()">{{ $t('shelf') }}:</template>
                    <template #right-text  class="pl-1">{{ record['inventory']['shelf_location'] }}</template>
                </column-wrapped>
                <column-wrapped>
                    <template #left-text @click="toggleDetails()">{{ $t('in stock') }}:</template>
                    <template #right-text class="pl-1">{{ dashIfZero(Number(record['inventory']['quantity'])) }}</template>
                </column-wrapped>
            </template>
        </div>
    </div>
  </div>
</template>

<script>

import helpers from "./../../mixins/helpers";
import url from "../../mixins/url";
import ColumnWrapped from "../Orders/ColumnWrapped.vue";

export default {
    components: {ColumnWrapped},
    mixins: [helpers, url],
    props: {
        record: {
            type: Object,
            required: true
        }
    },
    data: function() {
        return {
            showDetails: false
        };
    },

    methods: {
        toggleDetails: function() {
            this.showDetails = !this.showDetails;
        },
        getLinkInvetoryMovementsReport(param, value) {
            return `/reports/inventory-movements?${param}=${value}`;
        }
    }
}
</script>
