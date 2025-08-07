<?php

namespace App\Modules\Reports\src\Models;

use App\Models\OrderProduct;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;

class OrderProductReport extends Report
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->report_name = 'Orders';

        $this->baseQuery = OrderProduct::query()
            ->leftJoin('products as product', 'product.id', '=', 'orders_products.product_id')
            ->leftJoin('orders as order', 'order.id', '=', 'orders_products.order_id');

        $this->addFilter(
            AllowedFilter::callback('search_contains', function ($query, $value) {
                $query->whereLike('product.sku', "$value%")->orWhereLike('order.order_number', "%$value%");
            })
        );

        $this->addField('Order Placed At', 'order.order_placed_at', 'datetime', hidden: false);
        $this->addField('Order Number', 'order.order_number', hidden: false);
        $this->addField('Product ID', 'orders_products.product_id');
        $this->addField('Product SKU', 'product.sku', hidden: false);
        $this->addField('Product Name', 'product.name');
        $this->addField('SKU Ordered', 'orders_products.sku_ordered');
        $this->addField('Name Ordered', 'orders_products.name_ordered', hidden: false);
        $this->addField('Shipped', DB::raw("CASE WHEN orders_products.is_shipped = 1 THEN 'Yes' ELSE 'No' END"));
        $this->addField('Price', 'orders_products.price', 'numeric');
        $this->addField('Unit Full Price', 'orders_products.unit_full_price', 'numeric');
        $this->addField('Unit Discount', 'orders_products.unit_discount', 'numeric');
        $this->addField('Unit Sold Price', 'orders_products.unit_sold_price', 'numeric');
        $this->addField('Total Price', 'orders_products.total_price', 'numeric', hidden: false);
        $this->addField('Total Sold Price', 'orders_products.total_sold_price', 'numeric', hidden: false);
        $this->addField('Total Discount', 'orders_products.total_discount', 'numeric', hidden: false);
        $this->addField('Total Products Shipped', 'orders_products.total_products_shipped', 'numeric');
        $this->addField('Tax Rate', 'orders_products.tax_rate', 'numeric');
        $this->addField('Unit Tax', 'orders_products.unit_tax', 'numeric');
        $this->addField('Total Tax', 'orders_products.total_tax', 'numeric');
        $this->addField('Quantity Ordered', 'orders_products.quantity_ordered', 'numeric', hidden: false);
        $this->addField('Quantity To Ship', 'orders_products.quantity_to_ship', 'numeric', hidden: false);
        $this->addField('Quantity To Pick', 'orders_products.quantity_to_pick', 'numeric', hidden: false);
        $this->addField('Quantity Split', 'orders_products.quantity_split', 'numeric');
        $this->addField('Quantity Shipped', 'orders_products.quantity_shipped', 'numeric');
        $this->addField('Quantity Invoiced', 'orders_products.quantity_invoiced', 'numeric');
        $this->addField('Quantity Picked', 'orders_products.quantity_picked', 'numeric');
        $this->addField('Quantity Skipped Picking', 'orders_products.quantity_skipped_picking', 'numeric');
        $this->addField('Quantity Not Picked', 'orders_products.quantity_not_picked', 'numeric');
        $this->addField('Product Department', 'product.department');
        $this->addField('Product Category', 'product.category');
        $this->addField('Product Commodity Code', 'product.commodity_code');
        $this->addField('Product Default Tax Code', 'product.default_tax_code');
        $this->addField('Product Quantity', 'product.quantity', 'numeric');
        $this->addField('Product Quantity Reserved', 'product.quantity_reserved', 'numeric');
        $this->addField('Product Quantity Available', 'product.quantity_available', 'numeric');
        $this->addField('Product Supplier', 'product.supplier');
        $this->addField('Inventory Source Warehouse ID', 'inventory_source_warehouse_id', 'numeric');
        $this->addField('Inventory Source Warehouse Code', 'inventory_source_warehouse_code');
        $this->addField('Inventory Source Shelf Location', 'inventory_source_shelf_location');
        $this->addField('Inventory Source Quantity', 'inventory_source_quantity', 'numeric');
        $this->addField('Inventory Source Product ID', 'inventory_source_product_id', 'numeric');
        $this->addField('Order Status Code', 'order.status_code');
        $this->addField('Order Recount Required', 'order.recount_required');
        $this->addField('Order Label Template', 'order.label_template');
        $this->addField('Order Is Active', DB::raw("CASE WHEN order.is_active = 1 THEN 'Yes' ELSE 'No' END"));
        $this->addField('Order Is On Hold', DB::raw("CASE WHEN order.is_on_hold = 1 THEN 'Yes' ELSE 'No' END"));
        $this->addField('Order Is Editing', DB::raw("CASE WHEN order.is_editing = 1 THEN 'Yes' ELSE 'No' END"));
        $this->addField('Order Is Fully Paid', DB::raw("CASE WHEN order.is_fully_paid = 1 THEN 'Yes' ELSE 'No' END"));
        $this->addField('Order Product Line Count', 'order.product_line_count', 'numeric');
        $this->addField('Order Total Products', 'order.total_products', 'numeric');
        $this->addField('Order Total Shipping', 'order.total_shipping', 'numeric');
        $this->addField('Order Total Discounts', 'order.total_discounts', 'numeric');
        $this->addField('Order Total Order', 'order.total_order', 'numeric');
        $this->addField('Order Total Paid', 'order.total_paid', 'numeric');
        $this->addField('Order Total Outstanding', 'order.total_outstanding', 'numeric');
        $this->addField('Order Shipping Address ID', 'order.shipping_address_id', 'numeric');
        $this->addField('Order Billing Address ID', 'order.billing_address_id', 'numeric');
        $this->addField('Shipping Code', 'order.shipping_method_code');
        $this->addField('Shipping Name', 'order.shipping_method_name');
        $this->addField('Order Packer User ID', 'order.packer_user_id', 'numeric');
        $this->addField('Order Picked At', 'order.picked_at', 'datetime');
        $this->addField('Order Packed At', 'order.packed_at', 'datetime');
        $this->addField('Order Closed At', 'order.order_closed_at', 'datetime');
        $this->addField('Order Custom Unique Reference ID', 'order.custom_unique_reference_id');
    }
}
