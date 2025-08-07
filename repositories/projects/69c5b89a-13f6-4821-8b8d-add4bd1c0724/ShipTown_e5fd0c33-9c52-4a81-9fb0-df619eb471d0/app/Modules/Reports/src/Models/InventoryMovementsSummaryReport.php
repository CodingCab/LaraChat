<?php

namespace App\Modules\Reports\src\Models;

use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Warehouse;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;

class InventoryMovementsSummaryReport extends Report
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->report_name = 'Inventory Movements Summary';

        $this->baseQuery = InventoryMovement::query()
            ->leftJoin('inventory', 'inventory.id', '=', 'inventory_movements.inventory_id')
            ->leftJoin('products as products', 'inventory.product_id', '=', 'products.id');

        $this->addFilter(
            AllowedFilter::callback('search_contains', function ($query, $value) {
                $query->whereLike('products.sku', "$value%");
            })
        );

        $this->addFilter(
            AllowedFilter::callback('occurred_at_between', function ($query, $value) {
                if ((! is_array($value)) || count($value) !== 2) {
                    $query->whereRaw('1=2');

                    return;
                }

                // we are using datestamp for performance reasons
                // integer is way faster than datetime for filtering
                $fromDate = (integer) Carbon::parse($value[0])->format('Ymd');
                $toDate = (integer) Carbon::parse($value[1])->format('Ymd');
                $query->whereBetween('inventory_movements.occurred_at_datestamp', [$fromDate, $toDate]);

                $fromDateTime = Carbon::parse($value[0])->toDateTimeString();
                $toDateTime = Carbon::parse($value[1])->toDateTimeString();
                $query->whereBetween('inventory_movements.occurred_at', [$fromDateTime, $toDateTime]);
            })
        );

        $this->addField('id', 'inventory_movements.id', type: 'numeric');
        $this->addField('Date', 'inventory_movements.occurred_at_datestamp', 'integer', hidden: false, filterable: false);
        $this->addField('Occurred At', expression: 'inventory_movements.occurred_at', type: 'datetime', filterable: false);
        $this->addField('Warehouse Code', DB::raw('IFNULL(inventory.warehouse_code, "")'), hidden: false);
        $this->addField('Type', DB::raw('IFNULL(inventory_movements.type, "")'), hidden: false);

        $this->addField('Product SKU', 'products.sku');
        $this->addField('Product Name', 'products.name');
        $this->addField('Product Department', 'products.department', hidden: false);
        $this->addField('Product Category', 'products.category');


        $this->addField('quantity', DB::raw('inventory_movements.quantity_delta'), 'numeric', grouping: 'sum');
        $this->addField('Total Cost', DB::raw('round(inventory_movements.quantity_delta * inventory_movements.unit_cost, 3)'), 'numeric', hidden: false, grouping: 'sum');
        $this->addField('Total Retail', DB::raw('round(inventory_movements.quantity_delta * inventory_movements.unit_price, 3)'), 'numeric', hidden: false, grouping: 'sum');

        // this is handy in stocktake movements
        $this->addField('Shorts', DB::raw('round(inventory_movements.quantity_delta < 0, 3)'), 'numeric', grouping: 'sum');
        $this->addField('Overs', DB::raw('round(inventory_movements.quantity_delta > 0, 3)'), 'numeric', grouping: 'sum');
        $this->addField('Correct', DB::raw('round(inventory_movements.quantity_delta = 0, 3)'), 'numeric', grouping: 'sum');

        $this->addField('description', 'inventory_movements.description');
        $this->addField('unique id', 'inventory_movements.custom_unique_reference_id');
        $this->addField('count', DB::raw('(1)'), 'numeric', grouping: 'sum');
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
