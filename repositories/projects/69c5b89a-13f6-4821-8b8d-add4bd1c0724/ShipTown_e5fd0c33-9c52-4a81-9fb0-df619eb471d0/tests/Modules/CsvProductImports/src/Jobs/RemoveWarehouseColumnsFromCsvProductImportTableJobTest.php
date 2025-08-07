<?php

namespace Tests\Modules\CsvProductImports\src\Jobs;

use Tests\TestCase;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Schema;
use App\Modules\CsvProductImports\src\Jobs\AddWarehouseColumnsToCsvProductImportTableJob;
use App\Modules\CsvProductImports\src\Jobs\RemoveWarehouseColumnsFromCsvProductImportTableJob;

class RemoveWarehouseColumnsFromCsvProductImportTableJobTest extends TestCase
{
    public function test_job_removes_warehouse_columns()
    {
        /** @var Warehouse $warehouse */
        $warehouse = Warehouse::factory()->create();
        AddWarehouseColumnsToCsvProductImportTableJob::dispatchSync();

        $this->assertTrue(Schema::hasColumn('modules_csv_product_imports', "price_$warehouse->code"));
        $this->assertTrue(Schema::hasColumn('modules_csv_product_imports', "sale_price_$warehouse->code"));
        $this->assertTrue(Schema::hasColumn('modules_csv_product_imports', "sale_price_start_date_$warehouse->code"));
        $this->assertTrue(Schema::hasColumn('modules_csv_product_imports', "sale_price_end_date_$warehouse->code"));
        $this->assertTrue(Schema::hasColumn('modules_csv_product_imports', "restock_level_$warehouse->code"));
        $this->assertTrue(Schema::hasColumn('modules_csv_product_imports', "reorder_point_$warehouse->code"));
        $this->assertTrue(Schema::hasColumn('modules_csv_product_imports', "shelve_location_$warehouse->code"));

        RemoveWarehouseColumnsFromCsvProductImportTableJob::dispatchSync($warehouse);

        $this->assertFalse(Schema::hasColumn('modules_csv_product_imports', "price_$warehouse->code"));
        $this->assertFalse(Schema::hasColumn('modules_csv_product_imports', "sale_price_$warehouse->code"));
        $this->assertFalse(Schema::hasColumn('modules_csv_product_imports', "sale_price_start_date_$warehouse->code"));
        $this->assertFalse(Schema::hasColumn('modules_csv_product_imports', "sale_price_end_date_$warehouse->code"));
        $this->assertFalse(Schema::hasColumn('modules_csv_product_imports', "restock_level_$warehouse->code"));
        $this->assertFalse(Schema::hasColumn('modules_csv_product_imports', "reorder_point_$warehouse->code"));
        $this->assertFalse(Schema::hasColumn('modules_csv_product_imports', "shelve_location_$warehouse->code"));
    }
}
