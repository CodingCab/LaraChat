<?php

namespace Tests\Modules\CsvProductImports\src\Jobs;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Schema;
use App\Modules\CsvProductImports\src\Jobs\AddWarehouseColumnsToCsvProductImportTableJob;

class AddWarehouseColumnsToCsvProductImportTableJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job()
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
    }
}
