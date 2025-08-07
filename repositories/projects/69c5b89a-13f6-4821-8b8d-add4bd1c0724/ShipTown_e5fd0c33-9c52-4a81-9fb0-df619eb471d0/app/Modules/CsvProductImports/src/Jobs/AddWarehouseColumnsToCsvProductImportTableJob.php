<?php

namespace App\Modules\CsvProductImports\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Schema;

class AddWarehouseColumnsToCsvProductImportTableJob extends UniqueJob
{
    public function handle(): void
    {
        Warehouse::query()->each(function (Warehouse $warehouse) {
            Schema::table('modules_csv_product_imports', function ($table) use ($warehouse) {
                if (!Schema::hasColumn('modules_csv_product_imports', "price_$warehouse->code")) {
                    $table->decimal("price_$warehouse->code", 20, 3)->nullable()->after("supplier");
                    $table->decimal("sale_price_$warehouse->code", 20, 3)->nullable()->after("price_$warehouse->code");
                    $table->date("sale_price_start_date_$warehouse->code")->nullable()->after("sale_price_$warehouse->code");
                    $table->date("sale_price_end_date_$warehouse->code")->nullable()->after("sale_price_start_date_$warehouse->code");
                    $table->decimal("restock_level_$warehouse->code", 20, 3)->nullable()->after("sale_price_end_date_$warehouse->code");
                    $table->decimal("reorder_point_$warehouse->code", 20, 3)->nullable()->after("restock_level_$warehouse->code");
                    $table->string("shelve_location_$warehouse->code")->nullable()->after("reorder_point_$warehouse->code");
                }
            });
        });
    }
}
