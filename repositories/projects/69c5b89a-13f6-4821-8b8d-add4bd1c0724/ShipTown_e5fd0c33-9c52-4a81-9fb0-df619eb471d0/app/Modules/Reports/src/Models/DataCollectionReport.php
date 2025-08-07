<?php

namespace App\Modules\Reports\src\Models;

use App\Models\DataCollectionRecord;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;

class DataCollectionReport extends Report
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->report_name = 'Data Collection Report';

        $this->baseQuery = DataCollectionRecord::query()
            ->leftJoin('products as product', 'data_collection_records.product_id', '=', 'product.id')
            ->leftJoin('data_collections', 'data_collections.id', '=', 'data_collection_records.data_collection_id')
            ->leftJoin('inventory', function ($query) {
                return $query->on('data_collection_records.product_id', '=', 'inventory.product_id')
                    ->where('inventory.warehouse_id', auth()->user()->warehouse_id);
            })
            ->leftJoin('products_prices', function ($query) {
                return $query->on('data_collection_records.product_id', '=', 'products_prices.product_id')
                    ->where('products_prices.warehouse_id', auth()->user()->warehouse_id);
            })
            ->leftJoin('inventory_movements_statistics', function ($query) {
                return $query->on('inventory_movements_statistics.inventory_id', '=', 'inventory.id')
                    ->where('inventory_movements_statistics.type', '=', 'sale');
            });

        $this->allowedIncludes = [
            'product',
            'product.tags',
            'product.aliases',
            'dataCollection',
            'inventory',
            'products_prices',
            'prices',
            'discount',
        ];

        $this->addField('ID', 'data_collection_records.id', 'numeric');
        $this->addField('Inventory ID', 'data_collection_records.inventory_id', 'numeric');
        $this->addField('Product ID', 'data_collection_records.product_id', 'numeric');
        $this->addField('Data Collection ID', 'data_collection_records.data_collection_id', 'numeric');
        $this->addField('Warehouse ID', 'data_collections.warehouse_id', 'numeric');
        $this->addField('Product SKU', 'product.sku');
        $this->addField('Product Name', 'product.name');
        $this->addField('Quantity Requested', 'data_collection_records.quantity_requested', 'numeric');
        $this->addField('Total Transferred In', 'data_collection_records.total_transferred_in', 'numeric');
        $this->addField('Total Transferred Out', 'data_collection_records.total_transferred_out', 'numeric');
        $this->addField('Quantity Scanned', 'data_collection_records.quantity_scanned', 'numeric');
        $this->addField('Quantity To Scan', 'data_collection_records.quantity_to_scan', 'numeric');
        $this->addField('Unit Cost', 'data_collection_records.unit_cost', 'numeric');
        $this->addField('Unit Sold Price', 'data_collection_records.unit_sold_price', 'numeric');
        $this->addField('Unit Discount', 'data_collection_records.unit_discount', 'numeric');
        $this->addField('Unit Full Price', 'data_collection_records.unit_full_price', 'numeric');
        $this->addField('Total Cost', 'data_collection_records.total_cost', 'numeric');
        $this->addField('Total Sold Price', 'data_collection_records.total_sold_price', 'numeric');
        $this->addField('Total Full Price', 'data_collection_records.total_full_price', 'numeric');
        $this->addField('Total Discount', 'data_collection_records.total_discount', 'numeric');
        $this->addField('Total Profit', 'data_collection_records.total_profit', 'numeric');
        $this->addField('Total Price', 'data_collection_records.total_price', 'numeric');
        $this->addField('Total Adjusted Quantity', 'data_collection_records.total_adjusted_quantity', 'numeric');
        $this->addField('Total Adjusted Cost', 'data_collection_records.total_adjusted_cost', 'numeric');
        $this->addField('Total Adjusted Sold Price', 'data_collection_records.total_adjusted_sold_price', 'numeric');
        $this->addField('Sales Tax Code', 'data_collection_records.sales_tax_code');
        $this->addField('Unit Tax', 'data_collection_records.unit_tax', 'numeric');
        $this->addField('Calculated Unit Tax', 'data_collection_records.calculated_unit_tax', 'numeric');
        $this->addField('Total Tax', 'data_collection_records.total_tax', 'numeric');
        $this->addField('Calculated Total Tax', 'data_collection_records.calculated_total_tax', 'numeric');
        $this->addField('Price Source', 'data_collection_records.price_source');
        $this->addField('Price Source ID', 'data_collection_records.price_source_id');
        $this->addField('Comment', 'data_collection_records.comment');
        $this->addField('Inventory Quantity', 'inventory.quantity', 'numeric');
        $this->addField('Inventory Last Counted At', 'inventory.last_counted_at', 'datetime');
        $this->addField('Last Movement At', 'inventory.last_movement_at', 'datetime');
        $this->addField('Shelf Location', 'inventory.shelve_location');
        $this->addField('Product Cost', 'products_prices.cost', 'numeric');
        $this->addField('Product Price', 'products_prices.price', 'numeric');
        $this->addField('Product Sale Price', 'products_prices.sale_price', 'numeric');
        $this->addField('Product Sale Price Start Date', 'products_prices.sale_price_start_date', 'datetime');
        $this->addField('Product Sale Price End Date', 'products_prices.sale_price_end_date', 'datetime');
        $this->addField(
            'Last 7 Days Sales',
            DB::raw('(-1 * inventory_movements_statistics.last7days_quantity_delta)'),
            'numeric'
        );
        $this->addField(
            'Last 14 Days Sales',
            DB::raw('(-1 * inventory_movements_statistics.last14days_quantity_delta)'),
            'numeric'
        );
        $this->addField(
            'Last 28 Days Sales',
            DB::raw('(-1 * inventory_movements_statistics.last28days_quantity_delta)'),
            'numeric'
        );
        $this->addField('Is Requested', 'data_collection_records.is_requested', 'boolean');
        $this->addField('Is Fully Scanned', 'data_collection_records.is_fully_scanned', 'boolean');
        $this->addField('Is Over Scanned', 'data_collection_records.is_over_scanned', 'boolean');

        $this->addFilter(
            AllowedFilter::callback('has_tags', function ($query, $value) {
                $query->whereHas('product', function ($query) use ($value) {
                    $query->withAllTags($value);
                });
            })
        );

        $this->addFilter(
            AllowedFilter::scope('sku_or_alias', 'skuOrAlias'),
        );
    }
}
