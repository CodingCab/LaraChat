<?php

namespace App\Modules\CsvProductImports\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Schema;

class UpdateWarehouseColumnsInCsvProductImportTableJob extends UniqueJob
{
    protected Warehouse $warehouse;
    protected string $oldCode;

    public function __construct(Warehouse $warehouse, string $oldCode)
    {
        $this->warehouse = $warehouse;
        $this->oldCode = $oldCode;
    }

    public function handle(): void
    {
        if ($this->warehouse->code === $this->oldCode) {
            return;
        }

        $newCode = $this->warehouse->code;
        $oldCode = $this->oldCode;

        Schema::table('modules_csv_product_imports', function ($table) use ($newCode, $oldCode) {
            if (Schema::hasColumn('modules_csv_product_imports', "price_$oldCode")) {
                $table->renameColumn("price_$oldCode", "price_$newCode");
                $table->renameColumn("sale_price_$oldCode", "sale_price_$newCode");
                $table->renameColumn("sale_price_start_date_$oldCode", "sale_price_start_date_$newCode");
                $table->renameColumn("sale_price_end_date_$oldCode", "sale_price_end_date_$newCode");
                $table->renameColumn("restock_level_$oldCode", "restock_level_$newCode");
                $table->renameColumn("reorder_point_$oldCode", "reorder_point_$newCode");
                $table->renameColumn("shelve_location_$oldCode", "shelve_location_$newCode");
            }
        });
    }
}
