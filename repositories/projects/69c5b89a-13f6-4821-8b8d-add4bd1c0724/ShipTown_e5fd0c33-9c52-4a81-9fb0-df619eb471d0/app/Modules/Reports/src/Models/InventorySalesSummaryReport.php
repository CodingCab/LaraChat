<?php

namespace App\Modules\Reports\src\Models;

use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Warehouse;
use App\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;

class InventorySalesSummaryReport extends Report
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->report_name = 'Inventory Movements Summary';

        $this->baseQuery = InventoryMovement::query()
            ->leftJoin('inventory', 'inventory.id', '=', 'inventory_movements.inventory_id')
            ->leftJoin('products as products', 'inventory.product_id', '=', 'products.id')
            ->where('inventory_movements.type', 'sale');

        $this->addFilter(
            AllowedFilter::callback('search_contains', function ($query, $value) {
                $query->whereLike('products.sku', "$value%");
            })
        );

        $this->addField('Occurred At', expression: 'inventory_movements.occurred_at', type: 'datetime');
        $this->addField('SKU', 'products.sku');
        $this->addField('Name', 'products.name');
        $this->addField('Supplier', 'products.supplier');
        $this->addField('Department', 'products.department', hidden: false);
        $this->addField('Category', 'products.category');
        $this->addField('Quantity Sold', DB::raw('inventory_movements.quantity_delta * -1'), 'numeric', grouping: 'sum');
        $this->addField('Total Sales', DB::raw('round(inventory_movements.quantity_delta * inventory_movements.unit_price * -1, 3)'), 'numeric', hidden: false, grouping: 'sum');
        $this->addField('Total Cost', DB::raw('inventory_movements.quantity_delta * inventory_movements.unit_cost * -1'), 'numeric', grouping: 'sum');
        $this->addField('Total Tax', DB::raw("(inventory_movements.quantity_delta * inventory_movements.unit_tax * -1)"), 'numeric', grouping: 'sum');
        $this->addField('Total Profit', DB::raw("(inventory_movements.quantity_delta * (inventory_movements.unit_price - inventory_movements.unit_cost - inventory_movements.unit_tax) * -1)"), 'numeric', grouping: 'sum');

        Warehouse::query()->orderBy('code')->each(function ($warehouse) {
            $this->addField('Sales ' . $warehouse->code, DB::raw("(case when inventory.warehouse_code = '{$warehouse->code}' then inventory_movements.quantity_delta * inventory_movements.unit_price * -1 else 0 end)"), 'numeric', hidden: false, grouping: 'sum');
            $this->addField('Cost ' . $warehouse->code, DB::raw("(case when inventory.warehouse_code = '{$warehouse->code}' then inventory_movements.quantity_delta * inventory_movements.unit_cost * -1 else 0 end)"), 'numeric', grouping: 'sum');
            $this->addField('Tax ' . $warehouse->code, DB::raw("(case when inventory.warehouse_code = '{$warehouse->code}' then inventory_movements.quantity_delta * inventory_movements.unit_tax * -1 else 0 end)"), 'numeric', grouping: 'sum');
            $this->addField('Profit ' . $warehouse->code, DB::raw("(case when inventory.warehouse_code = '{$warehouse->code}' then inventory_movements.quantity_delta * (inventory_movements.unit_price - inventory_movements.unit_cost - inventory_movements.unit_tax) * -1 else 0 end)"), 'numeric', grouping: 'sum');
            $this->addField('Quantity Sold ' . $warehouse->code, DB::raw("(case when inventory.warehouse_code = '{$warehouse->code}' then inventory_movements.quantity_delta * -1 else 0 end)"), 'numeric', grouping: 'sum');
            $this->addField('Stock' . ' ' . $warehouse->code, DB::raw("(SELECT IFNULL(SUM(inv.quantity), 0) FROM inventory as inv WHERE inv.product_id = products.id AND inv.warehouse_code = '{$warehouse->code}')"), 'numeric', hidden: true, grouping: 'sum');
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}
