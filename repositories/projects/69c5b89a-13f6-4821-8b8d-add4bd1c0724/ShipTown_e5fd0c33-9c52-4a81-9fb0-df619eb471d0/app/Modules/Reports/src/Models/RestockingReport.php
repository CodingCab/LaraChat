<?php

namespace App\Modules\Reports\src\Models;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\Tags\Tag;

class RestockingReport extends Report
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->report_name = 'Restocking Report';
        $warehouseCodes = Warehouse::withAnyTagsOfAnyType('fulfilment')->get('code');

        $this->baseQuery = Inventory::query()
            ->leftJoin('products', 'products.id', '=', 'inventory.product_id')
            ->leftJoin('products_prices', 'products_prices.inventory_id', '=', 'inventory.id')
            ->leftJoin('inventory_movements_statistics', function (JoinClause $join) {
                $join->on('inventory.id', '=', 'inventory_movements_statistics.inventory_id');
                $join->where('inventory_movements_statistics.type', 'sale');
            })
            ->join('inventory as fulfilment_center', function (JoinClause $join) use ($warehouseCodes) {
                $join->on('fulfilment_center.product_id', '=', 'inventory.product_id');
                $join->on('fulfilment_center.warehouse_code', '!=', 'inventory.warehouse_code')
                    ->whereIn('fulfilment_center.warehouse_code', $warehouseCodes->toArray());
            });

        $this->defaultSort = '-quantity_required';
        $this->allowedIncludes = ['product', 'product.tags', 'product.prices', 'movementsStatistics'];

        $this->addField('Warehouse Code', 'inventory.warehouse_code', hidden: false);
        $this->addField('Product SKU', 'products.sku', hidden: false);
        $this->addField('Product Name', 'products.name', hidden: false);
        $this->addField('Price', 'products_prices.price', 'numeric');
        $this->addField('Product Department', 'products.department');
        $this->addField('Product Category', 'products.category');
        $this->addField('Sale Price', 'products_prices.sale_price', 'numeric');
        $this->addField('Sale Start Date', 'products_prices.sale_price_start_date', 'datetime');
        $this->addField('Sale End Date', 'products_prices.sale_price_end_date', 'datetime');
        $this->addField('Quantity Incoming', 'inventory.quantity_incoming', 'numeric', hidden: false);
        $this->addField('Quantity Required', 'inventory.quantity_required', 'numeric', hidden: false);
        $this->addField('FC Quantity Available', DB::raw('IFNULL(fulfilment_center.quantity_available, 0)'), 'numeric', hidden: false);
        $this->addField('Reorder Point', 'inventory.reorder_point', 'numeric', hidden: false);
        $this->addField('Restock Level', 'inventory.restock_level', 'numeric', hidden: false);
        $this->addField('Quantity Available', 'inventory.quantity_available', 'numeric', hidden: false);
        $this->addField('Quantity In Stock', 'inventory.quantity', 'numeric', hidden: false);
        $this->addField('Last Movement At', 'inventory.last_movement_at', 'datetime');
        $this->addField('Last Sold At', 'inventory.last_sold_at', 'datetime', hidden: false);
        $this->addField('First Sold At', 'inventory.first_sold_at', 'datetime');
        $this->addField('Last Counted At', 'inventory.last_counted_at', 'datetime');
        $this->addField('First Received At', 'inventory.first_received_at', 'datetime');
        $this->addField('Last Received At', 'inventory.last_received_at', 'datetime');
        $this->addField('Last 7 Days Sales Quantity Delta', DB::raw('IFNULL(inventory_movements_statistics.last7days_quantity_delta, 0)'), 'numeric');
        $this->addField('Last 14 Days Sales Quantity Delta', DB::raw('IFNULL(inventory_movements_statistics.last14days_quantity_delta, 0)'), 'numeric');
        $this->addField('Last 28 Days Sales Quantity Delta', DB::raw('IFNULL(inventory_movements_statistics.last28days_quantity_delta, 0)'), 'numeric');
        $this->addField('Quantity Sold Last 7 Days', DB::raw('IFNULL(inventory_movements_statistics.last7days_quantity_delta * -1, 0)'), 'numeric');
        $this->addField('Quantity Sold Last 14 Days', DB::raw('IFNULL(inventory_movements_statistics.last14days_quantity_delta * -1, 0)'), 'numeric');
        $this->addField('Quantity Sold Last 28 Days', DB::raw('IFNULL(inventory_movements_statistics.last28days_quantity_delta * -1, 0)'), 'numeric');
        $this->addField('Fulfilment Center', 'fulfilment_center.warehouse_code', hidden: false);
        $this->addField('Warehouse Has Stock', 'fulfilment_center.is_in_stock', 'boolean', hidden: false);
        $this->addField('ID', 'inventory.id', 'numeric');
        $this->addField('Product ID', 'inventory.product_id', 'numeric');
        $this->addField('Inventory ID', 'inventory.id', 'numeric');
        $this->addField('Warehouse ID', 'inventory.warehouse_id', 'numeric');
        $this->addField('FC Shelf Location', 'fulfilment_center.shelve_location');
        $this->addField('FC Quantity Incoming', 'fulfilment_center.quantity_incoming', 'numeric');

        $this->addFilter(
            AllowedFilter::callback('product_has_tags_containing', function ($query, $value) {
                $tags = Tag::containing($value)->get('id');

                $productsQuery = Product::withAnyTags($tags)->select('id');

                return $query->whereIn('inventory.product_id', $productsQuery->get()->toArray());
            })
        );

        $this->addFilter(
            AllowedFilter::callback('product_has_tags', function ($query, $value) {
                $tags = Tag::findFromStringOfAnyType($value);

                $productsQuery = Product::withAnyTags($tags)->select('id');

                return $query->whereIn('inventory.product_id', $productsQuery);
            })
        );

        $this->addFilter(
            AllowedFilter::callback('search_contains', function ($query, $value) {
                $query->where(function ($query) use ($value) {
                    $query->where('products.sku', 'like', '%'.$value.'%')
                        ->orWhere('products.name', 'like', '%'.$value.'%');
                });
            })
        );
    }
}
