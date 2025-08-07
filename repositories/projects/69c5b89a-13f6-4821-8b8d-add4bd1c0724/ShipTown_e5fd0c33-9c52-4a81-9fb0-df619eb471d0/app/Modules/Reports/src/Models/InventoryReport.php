<?php

namespace App\Modules\Reports\src\Models;

use App\Models\Inventory;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;

class InventoryReport extends Report
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->report_name = 'Inventory';

        $this->baseQuery = Inventory::query()
            ->leftJoin('products as product', 'inventory.product_id', '=', 'product.id')
            ->leftJoin('products_prices as product_prices', function ($join) {
                $join->on('inventory.product_id', '=', 'product_prices.product_id')
                    ->on('inventory.warehouse_id', '=', 'product_prices.warehouse_id');
            })
            ->leftJoin('inventory_movements_statistics', function ($join) {
                $join->on('inventory.id', '=', 'inventory_movements_statistics.inventory_id')
                    ->on('inventory.product_id', '=', 'inventory_movements_statistics.product_id')
                    ->on('inventory.warehouse_code', '=', 'inventory_movements_statistics.warehouse_code')
                    ->where('inventory_movements_statistics.type', '=', 'sale');
            });

        $this->addFilter(
            AllowedFilter::callback('search_contains', function ($query, $value) {
                $query->whereLike('product.sku', "$value%");
            })
        );

        $this->addField('SKU', 'product.sku', hidden: false);
        $this->addField('Name', 'product.name', hidden: false);
        $this->addField('Department', 'product.department');
        $this->addField('Category', 'product.category');
        $this->addField('Supplier', 'product.supplier', hidden: false);
        $this->addField('Warehouse Code', 'inventory.warehouse_code', hidden: false);
        $this->addField('Sales 7 Days', DB::raw('COALESCE(inventory_movements_statistics.last7days_quantity_delta, 0) * -1'), 'numeric');
        $this->addField('Sales 14 Days', DB::raw('COALESCE(inventory_movements_statistics.last14days_quantity_delta, 0) * -1'), 'numeric', hidden: false);
        $this->addField('Sales 28 Days', DB::raw('COALESCE(inventory_movements_statistics.last28days_quantity_delta, 0) * -1'), 'numeric');
        $this->addField('Quantity Warehouse', 'inventory.quantity', 'numeric', hidden: false);
        $this->addField('Quantity Reserved', 'inventory.quantity_reserved', 'numeric', hidden: false);
        $this->addField('Quantity Available', 'inventory.quantity_available', 'numeric', hidden: false);
        $this->addField('Quantity Incoming', 'inventory.quantity_incoming', 'numeric', hidden: false);
        $this->addField('Quantity Required', 'inventory.quantity_required', 'numeric', hidden: false);
        $this->addField('Reorder Point', 'inventory.reorder_point', 'numeric');
        $this->addField('Restock Level', 'inventory.restock_level', 'numeric');

        $this->addField('Price', 'product_prices.price', 'numeric');
        $this->addField('Cost', 'product_prices.cost', 'numeric');
        $this->addField('Total Price', DB::raw('ROUND(product_prices.price * inventory.quantity, 2)'), 'numeric', hidden: false);
        $this->addField('Total Cost', DB::raw('ROUND(product_prices.cost * inventory.quantity, 2)'), 'numeric', hidden: false);

        $this->addField('Sale Price', 'product_prices.sale_price', 'numeric');
        $this->addField('Sale Price Start Date', 'product_prices.sale_price_start_date', 'datetime');
        $this->addField('Sale Price End Date', 'product_prices.sale_price_end_date', 'datetime');

        $this->addField('Supplier', 'product.supplier');
        $this->addField('Shelf Location', 'inventory.shelve_location');

        $this->addField('Reservations', DB::raw('SELECT GROUP_CONCAT(concat(quantity_reserved, \' - \', comment) SEPARATOR \', \') FROM `inventory_reservations` WHERE inventory_reservations.inventory_id = inventory.id'), 'string');

        $this->addField('First Movement At', 'inventory.first_movement_at', 'datetime');
        $this->addField('Last Movement At', 'inventory.last_movement_at', 'datetime');
        $this->addField('First Received At', 'inventory.first_received_at', 'datetime');
        $this->addField('Last Received At', 'inventory.last_received_at', 'datetime');
        $this->addField('First Sold At', 'inventory.first_sold_at', 'datetime');
        $this->addField('Last Sold At', 'inventory.last_sold_at', 'datetime');
        $this->addField('First Counted At', 'inventory.first_counted_at', 'datetime');
        $this->addField('Last Counted At', 'inventory.last_counted_at', 'datetime');
        $this->addField('Last Movement ID', 'inventory.last_movement_id', 'datetime');
        $this->addField('Deleted At', 'inventory.deleted_at', 'datetime');
        $this->addField('Created At', 'inventory.created_at', 'datetime');
        $this->addField('Updated At', 'inventory.updated_at', 'datetime');

        $this->addField('Last Sequence Number', 'inventory.last_sequence_number', 'numeric');
        $this->addField('Recount Required', 'inventory.recount_required');
        $this->addField('Product ID', 'inventory.product_id', 'numeric');
        $this->addField('ID', 'inventory.id', 'numeric');
        $this->addField('Warehouse ID', 'inventory.warehouse_id', 'numeric');


        $this->addFilter(
            AllowedFilter::callback('has_tags', function ($query, $value) {
                $query->whereHas('product', function ($query) use ($value) {
                    $query->withAllTags($value);
                });
            })
        );
    }
}
