<?php

namespace App\Modules\Reports\src\Models;

use App\Models\DataCollectionRecord;
use Spatie\QueryBuilder\AllowedFilter;

class InventoryTransferReport extends Report
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->report_name = 'Inventory Transfers';

        $this->baseQuery = DataCollectionRecord::query()
            ->leftJoin('data_collections', 'data_collections.id', '=', 'data_collection_records.data_collection_id')
            ->leftJoin('products', 'products.id', '=', 'data_collection_records.product_id')
            ->leftJoin('products_prices', function ($join) {
                $join->on('products_prices.product_id', '=', 'data_collection_records.product_id')
                    ->whereColumn('products_prices.warehouse_id', 'data_collections.warehouse_id');
            })
            ->leftJoin('warehouses', 'warehouses.id', '=', 'data_collections.warehouse_id')
            ->leftJoin(
                'warehouses as destination_warehouses',
                'destination_warehouses.code',
                '=',
                'data_collections.destination_warehouse_code'
            );

        $this->addFilter(
            AllowedFilter::callback('search_contains', function ($query, $value) {
                $query->whereLike('products.sku', "$value%");
            })
        );

        $this->addField('ID', 'data_collection_records.id', 'numeric', hidden: false);
        $this->addField('Created At', 'data_collections.created_at', 'datetime', hidden: false);
        $this->addField('Warehouse Code', 'warehouses.code', hidden: false);
        $this->addField('Destination Warehouse Code', 'destination_warehouses.code', hidden: false);
        $this->addField('Destination Warehouse Name', 'destination_warehouses.name', hidden: false);
        $this->addField('Department', 'products.department');
        $this->addField('Product SKU', 'products.sku', hidden: false);
        $this->addField('Product Name', 'products.name', hidden: false);
        $this->addField(
            'Quantity Requested',
            'data_collection_records.quantity_requested',
            'numeric',
            grouping: 'sum',
            hidden: false
        );
        $this->addField(
            'Transferred Out',
            'data_collection_records.total_transferred_out',
            'numeric',
            grouping: 'sum',
            hidden: false
        );
        $this->addField(
            'Transferred In',
            'data_collection_records.total_transferred_in',
            'numeric',
            grouping: 'sum',
            hidden: false
        );
        $this->addField(
            'Total Adjusted Quantity',
            'data_collection_records.total_adjusted_quantity',
            'numeric',
            grouping: 'sum',
            hidden: false
        );
        $this->addField('Unit Cost', 'data_collection_records.unit_cost', 'numeric', grouping: 'max');
        $this->addField('Unit Price', 'data_collection_records.unit_sold_price', 'numeric', grouping: 'max');
        $this->addField(
            'Total Cost',
            'data_collection_records.total_cost',
            'numeric',
            grouping: 'max',
            hidden: false
        );
        $this->addField(
            'Total Price',
            'data_collection_records.total_sold_price',
            'numeric',
            grouping: 'max',
            hidden: false
        );
        $this->addField(
            'Total Adjusted Cost',
            'data_collection_records.total_adjusted_cost',
            'numeric',
            grouping: 'sum',
            hidden: false
        );
        $this->addField(
            'Total Adjusted Sold Price',
            'data_collection_records.total_adjusted_sold_price',
            'numeric',
            grouping: 'sum',
            hidden: false
        );
        $this->addField('Category', 'products.category');
        $this->addField('Transfer Name', 'data_collections.name', hidden: false);
        $this->addField('Updated At', 'data_collection_records.updated_at', 'datetime');
        $this->addField('Archived At', 'data_collections.deleted_at', 'datetime');

        $this->allowedIncludes = [
            'product',
            'product.tags',
            'product.aliases',
            'dataCollection',
            'inventory',
            'products_prices',
        ];

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
