<?php

namespace App\Modules\Reports\src\Models;

use App\Models\InventoryMovement;
use Spatie\QueryBuilder\AllowedFilter;

class StocktakesReport extends Report
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->view = 'reports.inventory-movements';
        $this->report_name = 'Stocktakes Report';

        $this->baseQuery = InventoryMovement::query()
            ->leftJoin('inventory', function ($join) {
                $join->on('inventory_movements.product_id', '=', 'inventory.product_id');
                $join->on('inventory_movements.warehouse_id', '=', 'inventory.warehouse_id');
            })
            ->leftJoin('products as product', 'inventory_movements.product_id', '=', 'product.id')
            ->leftJoin('users as user', 'inventory_movements.user_id', '=', 'user.id')
            ->where(['inventory_movements.description' => 'stocktake'])
            ->orderBy('inventory_movements.id', 'desc');

        $this->addFilter(
            AllowedFilter::callback('search_contains', function ($query, $value) {
                $query->whereLike('product.sku', "$value%");
            })
        );

        $this->fields = [
            'created_at' => 'inventory_movements.created_at',
            'warehouse_code' => 'inventory.warehouse_code',
            'user' => 'user.name',
            'product_sku' => 'product.sku',
            'product_name' => 'product.name',
            'quantity_delta' => 'inventory_movements.quantity_delta',
            'quantity_before' => 'inventory_movements.quantity_before',
            'quantity_after' => 'inventory_movements.quantity_after',
        ];

        $this->casts = [
            'date' => 'datetime',
            'quantity_delta' => 'float',
            'quantity_before' => 'float',
            'quantity_after' => 'float',
        ];

        $this->addFilter(
            AllowedFilter::callback('has_tags', function ($query, $value) {
                $query->whereHas('product', function ($query) use ($value) {
                    $query->withAllTags($value);
                });
            })
        );
    }
}
