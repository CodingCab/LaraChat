<?php

namespace App\Modules\Reports\src\Models;

use App\Models\Inventory;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;

class ProductsRequiredReport extends Report
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->report_name = 'Products required';

        $this->baseQuery = Inventory::query()
            ->join('products as product', 'inventory.product_id', '=', 'product.id')
            ->leftJoin('products_prices as price', function ($join) {
                $join->on('inventory.product_id', '=', 'price.product_id')
                    ->on('inventory.warehouse_id', '=', 'price.warehouse_id');
            })
            ->where('inventory.quantity_required', '>', 0);

        $this->addField('SKU', 'product.sku', hidden: false);
        $this->addField('Product Name', 'product.name', hidden: false);
        $this->addField('Product number', 'product.product_number', hidden: false);
        $this->addField('Carton Quantity', 'product.pack_quantity', 'numeric', hidden: false);
        $this->addField('Supplier', 'product.supplier', hidden: false);
        $this->addField('Cost', 'price.cost', 'numeric', hidden: false, grouping: 'avg');

        $quantityExpressions = [];

        Warehouse::query()->orderBy('code')->each(function ($warehouse) use (&$quantityExpressions) {
            $qtyExpr = "CASE WHEN inventory.warehouse_code = '{$warehouse->code}' THEN " .
                "CEILING(inventory.quantity_required / NULLIF(COALESCE(product.pack_quantity,1),0)) * NULLIF(COALESCE(product.pack_quantity,1),0) " .
                "ELSE 0 END";

            $this->addField(
                "Quantity {$warehouse->code}",
                DB::raw($qtyExpr),
                'numeric',
                hidden: false,
                grouping: 'sum'
            );
            $quantityExpressions[] = $qtyExpr;

            $priceExpr = "(SELECT price FROM products_prices WHERE products_prices.product_id = product.id AND products_prices.warehouse_code = '{$warehouse->code}' LIMIT 1)";
            $this->addField(
                "Price {$warehouse->code}",
                DB::raw($priceExpr),
                'numeric',
                hidden: false,
                grouping: 'avg'
            );
        });

        $totalExpression = '(' . implode(' + ', $quantityExpressions) . ')';
        $this->addField('Total (WH)', DB::raw($totalExpression), 'numeric', hidden: false, grouping: 'sum');
        $this->addField('Department', 'product.department', hidden: false);
        $this->addField('Category', 'product.category', hidden: false);
        $this->addField('Tags', DB::raw("(SELECT GROUP_CONCAT(JSON_UNQUOTE(JSON_EXTRACT(tags.name, '$.en')) SEPARATOR ', ') FROM taggables JOIN tags ON taggables.tag_id = tags.id WHERE taggables.taggable_type = 'App\\\\Models\\\\Product' AND taggables.taggable_id = product.id)"), hidden: false);

        $this->addFilter(
            AllowedFilter::callback('search_contains', function ($query, $value) {
                $query->whereLike('product.sku', "$value%");
            })
        );

        $this->addFilter(
            AllowedFilter::callback('has_tags', function ($query, $value) {
                $query->whereHas('product', function ($query) use ($value) {
                    $query->withAllTags($value);
                });
            })
        );
    }
}
