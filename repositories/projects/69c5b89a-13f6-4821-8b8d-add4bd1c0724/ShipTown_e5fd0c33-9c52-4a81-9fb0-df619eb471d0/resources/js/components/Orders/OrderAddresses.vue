<template>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="row col d-flex font-weight-bold pb-1 text-uppercase small text-secondary align-content-center text-center justify-content-center">
                    {{ $t('SHIPPING ADDRESS') }}
                    <li class="nav-item">
                        <a class="nav-link p-0 pl-2" @click.prevent="$emit('open-modal', order['shipping_address']['id'])" href="#">
                            <font-awesome-icon class="fas fa-sm" icon="pen-to-square" />
                        </a>
                    </li>
                </div>

                <column-wrapped :left-text="$t('email')">
                    <template #right-text>
                        <a :href="'mailto:' + order['shipping_address']['email'] + '?subject=Order #' + order['order_number']">{{ order['shipping_address']['email'] }} </a>
                    </template>
                </column-wrapped>

                <column-wrapped :left-text="$t('phone')">
                    <template #right-text>
                        <a :href="'tel:' + order['shipping_address']['phone']">{{ order['shipping_address']['phone'] }}</a>
                    </template>
                </column-wrapped>

                <br>

                <column-wrapped :left-text="$t('first_name')" :right-text="order['shipping_address']['first_name']" />
                <column-wrapped :left-text="$t('last_name')" :right-text="order['shipping_address']['last_name']" />
                <column-wrapped :left-text="$t('company')" :right-text="order['shipping_address']['company']" />
                <column-wrapped :left-text="$t('address1')" :right-text="order['shipping_address']['address1']" />
                <column-wrapped :left-text="$t('address2')" :right-text="order['shipping_address']['address2']" />
                <column-wrapped :left-text="$t('postcode')" :right-text="order['shipping_address']['postcode']" />
                <column-wrapped :left-text="$t('city')" :right-text="order['shipping_address']['city']" />
                <column-wrapped :left-text="$t('country_code')" :right-text="order['shipping_address']['country_code']" />
                <column-wrapped :left-text="$t('country_name')" :right-text="order['shipping_address']['country_name']" />
                <column-wrapped :left-text="$t('tax')" :right-text="order['shipping_address']['tax_id']" />
                <column-wrapped :left-text="$t('fax')" :right-text="order['shipping_address']['fax']" />
                <column-wrapped :left-text="$t('region')" :right-text="order['shipping_address']['region']" />
                <column-wrapped :left-text="$t('state_code')" :right-text="order['shipping_address']['state_code']" />
                <column-wrapped :left-text="$t('state_name')" :right-text="order['shipping_address']['state_name']" />
                <column-wrapped :left-text="$t('website')" :right-text="order['shipping_address']['website']" />
                <column-wrapped :left-text="$t('locker box code')" :right-text="order['shipping_address']['locker_box_code']" />

                <div v-if="order['billing_address']" class="mt-2">
                    <div class="row col d-block font-weight-bold pb-1 text-uppercase small text-secondary align-content-center text-center">
                        {{ $t('BILLING ADDRESS') }}
                    </div>

                    <column-wrapped :left-text="$t('email')">
                        <template #right-text>
                            <a :href="'mailto:' + order['billing_address']['email'] + '?subject=Order #' + order['order_number']">{{ order['billing_address']['email'] }} </a>
                        </template>
                    </column-wrapped>

                    <column-wrapped :left-text="$t('phone')">
                        <template #right-text>
                            <a :href="'tel:' + order['billing_address']['phone']">{{ order['billing_address']['phone'] }}</a>
                        </template>
                    </column-wrapped>
                    <column-wrapped :left-text="$t('first_name')" :right-text="order['billing_address']['first_name']" />
                    <column-wrapped :left-text="$t('last_name')" :right-text="order['billing_address']['last_name']" />
                    <column-wrapped :left-text="$t('company')" :right-text="order['billing_address']['company']" />
                    <column-wrapped :left-text="$t('address1')" :right-text="order['billing_address']['address1']" />
                    <column-wrapped :left-text="$t('address2')" :right-text="order['billing_address']['address2']" />
                    <column-wrapped :left-text="$t('postcode')" :right-text="order['billing_address']['postcode']" />
                    <column-wrapped :left-text="$t('city')" :right-text="order['billing_address']['city']" />
                    <column-wrapped :left-text="$t('country_code')" :right-text="order['billing_address']['country_code']" />
                    <column-wrapped :left-text="$t('country_name')" :right-text="order['billing_address']['country_name']" />
                    <column-wrapped :left-text="$t('tax')" :right-text="order['billing_address']['tax_id']" />
                    <column-wrapped :left-text="$t('fax')" :right-text="order['billing_address']['fax']" />
                    <column-wrapped :left-text="$t('region')" :right-text="order['billing_address']['region']" />
                    <column-wrapped :left-text="$t('state_code')" :right-text="order['billing_address']['state_code']" />
                    <column-wrapped :left-text="$t('state_name')" :right-text="order['billing_address']['state_name']" />
                    <column-wrapped :left-text="$t('website')" :right-text="order['billing_address']['website']" />
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import ColumnWrapped from './ColumnWrapped.vue';

export default {
    name: 'OrderAddresses',
    components: { ColumnWrapped },
    props: {
        order: {
            type: Object,
            required: true,
            default: () => ({})
        }
    }
};
</script>

