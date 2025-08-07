<?php

namespace App\Modules\CsvProductImports\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Schema;

class RemoveWarehouseColumnsFromCsvProductImportTableJob extends UniqueJob
{
    protected Warehouse $warehouse;

    public function __construct(Warehouse $warehouse)
    {
        $this->warehouse = $warehouse;
    }

    public function handle(): void
    {
        Schema::table('modules_csv_product_imports', function ($table) {
            if (!Schema::hasColumn('modules_csv_product_imports', "price_{$this->warehouse->code}")) {
                return;
            }

            $table->dropColumn([
                "price_{$this->warehouse->code}",
                "sale_price_{$this->warehouse->code}",
                "sale_price_start_date_{$this->warehouse->code}",
                "sale_price_end_date_{$this->warehouse->code}",
                "restock_level_{$this->warehouse->code}",
                "reorder_point_{$this->warehouse->code}",
                "shelve_location_{$this->warehouse->code}",
            ]);
        });
    }
}
