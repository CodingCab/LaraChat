<?php

namespace App\Modules\Reports\src\Models;

use App\Models\Inventory;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;

class InventoryTotalsReport extends Report
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
            ->where('inventory.quantity', '!=', 0);

        $this->addFilter(
            AllowedFilter::callback('search_contains', function ($query, $value) {
                $query->whereLike('product.sku', "$value%");
            })
        );

        $this->addField('SKU', 'product.sku', hidden: false);
        $this->addField('Name', 'product.name', hidden: false);
        $this->addField('Department', 'product.department');
        $this->addField('Supplier', 'product.supplier');

        $this->addField('Unit Cost', 'product_prices.price', 'numeric', grouping: 'avg');
        $this->addField('Unit Price', 'product_prices.cost', 'numeric', grouping: 'avg');
        $this->addField('Cost Value', 'ROUND(product_prices.cost * inventory.quantity, 2)', 'numeric', hidden: false, grouping: 'sum');
        $this->addField('Retail Value', 'ROUND(product_prices.price * inventory.quantity, 2)', 'numeric', hidden: false, grouping: 'sum');

        $this->addField('Quantity', DB::raw("inventory.quantity"), 'numeric', hidden: false, grouping: 'sum');

        Warehouse::query()->orderBy('code')->each(function ($warehouse) {
            $this->addField("{$warehouse->code}", DB::raw("(case when inventory.warehouse_code = '{$warehouse->code}' then inventory.quantity else 0 end)"), 'numeric', hidden: false, grouping: 'sum');
        });

        $this->addFilter(
            AllowedFilter::callback('has_tags', function ($query, $value) {
                $query->whereHas('product', function ($query) use ($value) {
                    $query->withAllTags($value);
                });
            })
        );
    }
}
