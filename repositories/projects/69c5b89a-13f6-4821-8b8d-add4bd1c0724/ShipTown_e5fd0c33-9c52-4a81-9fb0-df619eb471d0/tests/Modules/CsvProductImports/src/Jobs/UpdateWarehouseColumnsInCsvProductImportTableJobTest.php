<?php

namespace Tests\Modules\CsvProductImports\src\Jobs;

use Tests\TestCase;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Schema;
use App\Modules\CsvProductImports\src\Jobs\AddWarehouseColumnsToCsvProductImportTableJob;
use App\Modules\CsvProductImports\src\Jobs\UpdateWarehouseColumnsInCsvProductImportTableJob;

class UpdateWarehouseColumnsInCsvProductImportTableJobTest extends TestCase
{
    public function test_job_renames_warehouse_columns()
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

        $oldCode = $warehouse->code;
        $warehouse->update(['code' => $oldCode . 'U']);

        UpdateWarehouseColumnsInCsvProductImportTableJob::dispatchSync($warehouse, $oldCode);

        $this->assertFalse(Schema::hasColumn('modules_csv_product_imports', "price_$oldCode"));
        $this->assertFalse(Schema::hasColumn('modules_csv_product_imports', "sale_price_$oldCode"));
        $this->assertFalse(Schema::hasColumn('modules_csv_product_imports', "sale_price_start_date_$oldCode"));
        $this->assertFalse(Schema::hasColumn('modules_csv_product_imports', "sale_price_end_date_$oldCode"));
        $this->assertFalse(Schema::hasColumn('modules_csv_product_imports', "restock_level_$oldCode"));
        $this->assertFalse(Schema::hasColumn('modules_csv_product_imports', "reorder_point_$oldCode"));
        $this->assertFalse(Schema::hasColumn('modules_csv_product_imports', "shelve_location_$oldCode"));

        $this->assertTrue(Schema::hasColumn('modules_csv_product_imports', "price_$warehouse->code"));
        $this->assertTrue(Schema::hasColumn('modules_csv_product_imports', "sale_price_$warehouse->code"));
        $this->assertTrue(Schema::hasColumn('modules_csv_product_imports', "sale_price_start_date_$warehouse->code"));
        $this->assertTrue(Schema::hasColumn('modules_csv_product_imports', "sale_price_end_date_$warehouse->code"));
        $this->assertTrue(Schema::hasColumn('modules_csv_product_imports', "restock_level_$warehouse->code"));
        $this->assertTrue(Schema::hasColumn('modules_csv_product_imports', "reorder_point_$warehouse->code"));
        $this->assertTrue(Schema::hasColumn('modules_csv_product_imports', "shelve_location_$warehouse->code"));
    }

    public function test_job_does_nothing_if_code_is_the_same()
    {
        /** @var Warehouse $warehouse */
        $warehouse = Warehouse::factory()->create();

        AddWarehouseColumnsToCsvProductImportTableJob::dispatchSync();
        $this->assertTrue(Schema::hasColumn('modules_csv_product_imports', "price_$warehouse->code"));

        UpdateWarehouseColumnsInCsvProductImportTableJob::dispatchSync($warehouse, $warehouse->code);

        $this->assertTrue(Schema::hasColumn('modules_csv_product_imports', "price_$warehouse->code"));
        $this->assertTrue(Schema::hasColumn('modules_csv_product_imports', "sale_price_$warehouse->code"));
        $this->assertTrue(Schema::hasColumn('modules_csv_product_imports', "sale_price_start_date_$warehouse->code"));
        $this->assertTrue(Schema::hasColumn('modules_csv_product_imports', "sale_price_end_date_$warehouse->code"));
        $this->assertTrue(Schema::hasColumn('modules_csv_product_imports', "restock_level_$warehouse->code"));
        $this->assertTrue(Schema::hasColumn('modules_csv_product_imports', "reorder_point_$warehouse->code"));
        $this->assertTrue(Schema::hasColumn('modules_csv_product_imports', "shelve_location_$warehouse->code"));
    }
}
