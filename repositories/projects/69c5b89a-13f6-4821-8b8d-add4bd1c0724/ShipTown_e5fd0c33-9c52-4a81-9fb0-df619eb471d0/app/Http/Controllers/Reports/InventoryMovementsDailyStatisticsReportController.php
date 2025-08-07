<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Modules\InventoryMovementsDailyStatistics\src\Models\InventoryMovementsDailyStatistic;
use App\Modules\Reports\src\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryMovementsDailyStatisticsReportController extends Controller
{
    public function index(Request $request)
    {
        $report = Report::for(InventoryMovementsDailyStatistic::class);

        $report->addField('date', 'date', 'datetime', hidden: false)
            ->addField('warehouse_code', 'inventory_movements.warehouse_code', hidden: false)
            ->addField('product_sku', 'products.sku')
            ->addField('product_name', 'products.name')
            ->addField('product_department', 'products.department', hidden: false)
            ->addField('product_category', 'products.category')
            ->addField('quantity end of day', 'inventory_movements.quantity_after', type: 'numeric', grouping: 'sum')
            ->addField('unit_cost', 'inventory_movements.unit_cost', type: 'numeric', grouping: 'sum')
            ->addField('unit_price', 'products_prices.price', type: 'numeric', grouping: 'sum')
            ->addField('total_cost_value', DB::raw('inventory_movements.quantity_after * inventory_movements.unit_cost'), hidden: false, type: 'numeric', grouping: 'sum')
            ->addField('total_retail_value', DB::raw('inventory_movements.quantity_after * products_prices.price'), hidden: false, type: 'numeric', grouping: 'sum')
            ->addField('inventory movement id', 'inventory_movements.id');

        $report->baseQuery()
            ->leftJoin('inventory_movements', function ($join) {
                $join->on('inventory_movements.inventory_id', '=', 'inventory_movements_daily_statistics.inventory_id')
                    ->on('inventory_movements.id', '=', 'inventory_movements_daily_statistics.last_inventory_movement_id');
            })
            ->leftJoin('inventory', function ($join) {
                $join->on('inventory.id', '=', 'inventory_movements_daily_statistics.inventory_id');
            })
            ->leftJoin('products_prices', function ($join) {
                $join->on('products_prices.inventory_id', '=', 'inventory_movements.inventory_id');
            })
            ->leftJoin('products', function ($join) {
                $join->on('products.id', '=', 'inventory.product_id');
            });

        return $report->response();
    }
}
