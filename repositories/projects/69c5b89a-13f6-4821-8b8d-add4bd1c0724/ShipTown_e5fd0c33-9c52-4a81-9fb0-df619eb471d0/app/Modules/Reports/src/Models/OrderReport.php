<?php

namespace App\Modules\Reports\src\Models;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;

class OrderReport extends Report
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->report_name = t('Orders');

        $this->addFilter(
            AllowedFilter::callback('search_contains', function ($query, $value) {
                $query->whereLike('order_number', "%$value%");
            })
        );

        $this->baseQuery = Order::query()
            ->leftJoin('orders_addresses as order_addresses_shipping', 'orders.shipping_address_id', '=', 'order_addresses_shipping.id')
            ->leftJoin('orders_addresses as order_addresses_billing', 'orders.billing_address_id', '=', 'order_addresses_billing.id')
            ->leftJoin('orders_products_totals', 'orders.id', 'orders_products_totals.order_id');

        $this->addField('Order Placed At', 'orders.order_placed_at', 'datetime', hidden: false);
        $this->addField('Order Number', 'orders.order_number', hidden: false);
        $this->addField('Status Code', 'orders.status_code', hidden: false);
        $this->addField('Shipping Template', 'orders.label_template', hidden: false);
        $this->addField('Order Closed At', 'orders.order_closed_at', 'datetime');

        $this->addField('Lines Count', 'orders.product_line_count', 'numeric', hidden: false, grouping: 'sum');
        $this->addField('Total Products', 'orders.total_products', 'numeric', hidden: false, grouping: 'sum');
        $this->addField('Total Discounts', 'orders.total_discounts', 'numeric', hidden: false, grouping: 'sum');
        $this->addField('Total Shipping', 'orders.total_shipping', 'numeric', hidden: false, grouping: 'sum');
        $this->addField('Total Order', 'orders.total_order', 'numeric', hidden: false, grouping: 'sum');
        $this->addField('Total Paid', 'orders.total_paid', 'numeric', hidden: false, grouping: 'sum');
        $this->addField('Total Outstanding', 'orders.total_outstanding', 'numeric', hidden: false, grouping: 'sum');

        $this->addField('Count Product', 'orders_products_totals.count', 'numeric', grouping: 'sum');
        $this->addField('Quantity Ordered', 'orders_products_totals.quantity_ordered', 'numeric', grouping: 'sum');
        $this->addField('Quantity Split', 'orders_products_totals.quantity_split', 'numeric', grouping: 'sum');
        $this->addField('Total Price', 'orders_products_totals.total_price', 'numeric', grouping: 'sum');
        $this->addField('Quantity Picked', 'orders_products_totals.quantity_picked', 'numeric', grouping: 'sum');
        $this->addField('Quantity Skipped Picking', 'orders_products_totals.quantity_skipped_picking', 'numeric', grouping: 'sum');
        $this->addField('Quantity Not Picked', 'orders_products_totals.quantity_not_picked', 'numeric', grouping: 'sum');
        $this->addField('Quantity Shipped', 'orders_products_totals.quantity_shipped', 'numeric', grouping: 'sum');
        $this->addField('Total Products Shipped', 'orders_products_totals.total_products_shipped', 'numeric', grouping: 'sum');
        $this->addField('Quantity To Pick', 'orders_products_totals.quantity_to_pick', 'numeric', grouping: 'sum');
        $this->addField('Quantity To Ship', 'orders_products_totals.quantity_to_ship', 'numeric', grouping: 'sum');
        $this->addField('Max Updated At', 'orders_products_totals.max_updated_at', 'datetime');

        $this->addField(('Shipping Code'), 'orders.shipping_method_code');
        $this->addField(('Shipping Name'), 'orders.shipping_method_name');
        $this->addField(('Picked At'), 'orders.picked_at', 'datetime');
        $this->addField(('Packed At'), 'orders.packed_at', 'datetime');

        $this->addField(('Order Fully Paid'), DB::raw("CASE WHEN orders.is_fully_paid = 1 THEN '".t('Yes')."' ELSE '".t('No')."' END"));

        $this->addField(('Shipping Street Address 1'), 'order_addresses_shipping.address1');
        $this->addField(('Shipping Street Address 2'), 'order_addresses_shipping.address2');
        $this->addField(('Shipping Address Postcode'), 'order_addresses_shipping.postcode');
        $this->addField(('Shipping Address City'), 'order_addresses_shipping.city');
        $this->addField(('Shipping Address State Code'), 'order_addresses_shipping.state_code');
        $this->addField(('Shipping Address State'), 'order_addresses_shipping.state_name');
        $this->addField(('Shipping Address Country Code'), 'order_addresses_shipping.country_code');
        $this->addField(('Shipping Address Country'), 'order_addresses_shipping.country_name');
        $this->addField(('Shipping Address Fax'), 'order_addresses_shipping.fax');
        $this->addField(('Shipping Address Website'), 'order_addresses_shipping.website');
        $this->addField(('Shipping Address Region'), 'order_addresses_shipping.region');

        $this->addField(('Billing Street Address 1'), 'order_addresses_billing.address1');
        $this->addField(('Billing Street Address 2'), 'order_addresses_billing.address2');
        $this->addField(('Billing Address Postcode'), 'order_addresses_billing.postcode');
        $this->addField(('Billing Address City'), 'order_addresses_billing.city');
        $this->addField(('Billing Address State Code'), 'order_addresses_billing.state_code');
        $this->addField(('Billing Address State'), 'order_addresses_billing.state_name');
        $this->addField(('Billing Address Country Code'), 'order_addresses_billing.country_code');
        $this->addField(('Billing Address Country'), 'order_addresses_billing.country_name');
        $this->addField(('Billing Address Fax'), 'order_addresses_billing.fax');
        $this->addField(('Billing Address Website'), 'order_addresses_billing.website');
        $this->addField(('Billing Address Region'), 'order_addresses_billing.region');

        $this->addField(('Order Active'), DB::raw("CASE WHEN orders.is_active = 1 THEN 1 ELSE 0 END"), type: 'boolean', grouping: 'sum');
        $this->addField(('Order On Hold'), DB::raw("CASE WHEN orders.is_on_hold = 1 THEN 1 ELSE 0 END"), type: 'boolean', grouping: 'sum');
        $this->addField(('Order Editing'), DB::raw("CASE WHEN orders.is_editing = 1 THEN 1 ELSE 0 END"), type: 'boolean', grouping: 'sum');

        $this->addField(('Deleted At'), 'orders.deleted_at', 'datetime');
        $this->addField(('Created At'), 'orders.created_at', 'datetime');
        $this->addField(('Updated At'), 'orders.updated_at', 'datetime');
    }
}
