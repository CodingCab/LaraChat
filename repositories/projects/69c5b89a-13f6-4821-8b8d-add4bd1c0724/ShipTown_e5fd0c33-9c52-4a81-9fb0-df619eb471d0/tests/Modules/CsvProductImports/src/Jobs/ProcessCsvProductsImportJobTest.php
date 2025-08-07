<?php

namespace Tests\Modules\CsvProductImports\src\Jobs;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use App\Modules\CsvProductImports\src\Jobs\AddWarehouseColumnsToCsvProductImportTableJob;
use App\Modules\CsvProductImports\src\Jobs\ProcessCsvProductsImportJob;
use App\Modules\CsvProductImports\src\Models\CsvProductImport;
use App\Modules\CsvProductImports\src\Models\CsvUploadedFile;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProcessCsvProductsImportJobTest extends TestCase
{
    #[Test]
    public function it_imports_new_product_with_all_related_data()
    {
        $warehouse = Warehouse::factory()->create(['code' => 'MAIN']);
        AddWarehouseColumnsToCsvProductImportTableJob::dispatchSync();

        $csvUploadedFile = CsvUploadedFile::create([
            'filename' => 'test.csv',
            'mapped_fields' => [
                'sku' => 0,
                'name' => 1,
                'price' => 2,
                'sale_price' => 3,
                'alias' => 4,
                'tags_add' => 5,
                'price_MAIN' => 6,
                'sale_price_MAIN' => 7,
                'shelve_location_MAIN' => 8,
                'reorder_point_MAIN' => 9,
                'restock_level_MAIN' => 10,
            ],
        ]);

        $importRecord = CsvProductImport::factory()->create([
            'file_id' => $csvUploadedFile->id,
            'sku' => 'TEST-001',
            'name' => 'Test Product',
            'price' => 100,
            'sale_price' => 80,
            'alias' => 'test-alias',
            'tags_add' => 'tag1, tag2',
            'processed_at' => null,
            'price_MAIN' => 110,
            'sale_price_MAIN' => 90,
            'shelve_location_MAIN' => 'A-1',
            'reorder_point_MAIN' => 10,
            'restock_level_MAIN' => 20,
        ]);

        (new ProcessCsvProductsImportJob($csvUploadedFile->id))->handle();

        $this->assertDatabaseHas('products', [
            'sku' => 'TEST-001',
            'name' => 'Test Product',
            'price' => 100,
            'sale_price' => 80,
        ]);

        $product = Product::where('sku', 'TEST-001')->first();
        $this->assertNotNull($product);
        $this->assertEqualsCanonicalizing(['tag1', 'tag2'], $product->tags->pluck('name')->all());

        $this->assertDatabaseHas('products_aliases', [
            'product_id' => $product->id,
            'alias' => 'test-alias',
        ]);

        $this->assertDatabaseHas('inventory', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'shelve_location' => 'A-1',
            'reorder_point' => 10,
            'restock_level' => 20,
        ]);

        $this->assertDatabaseHas('products_prices', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'price' => 110,
            'sale_price' => 90,
        ]);

        $this->assertNotNull($importRecord->fresh()->processed_at);
    }

    #[Test]
    public function it_updates_existing_product_and_inventory()
    {
        $product = Product::factory()->create(['sku' => 'TEST-001', 'name' => 'Old Name']);
        $warehouse = Warehouse::factory()->create(['code' => 'MAIN']);
        AddWarehouseColumnsToCsvProductImportTableJob::dispatchSync();

        $inventory = Inventory::query()
            ->where('product_sku', 'TEST-001')
            ->where('warehouse_id', $warehouse->id)
            ->first();

        if (!$inventory) {
            $inventory = Inventory::factory()->create([
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'shelve_location' => 'OLD-LOC',
            ]);
        } else {
            $inventory->update(['shelve_location' => 'OLD-LOC']);
        }

        $csvUploadedFile = CsvUploadedFile::create([
            'filename' => 'update.csv',
            'mapped_fields' => [
                'sku' => 0,
                'name' => 1,
                'shelve_location_MAIN' => 2,
            ],
        ]);

        CsvProductImport::factory()->create([
            'file_id' => $csvUploadedFile->id,
            'sku' => 'TEST-001',
            'name' => 'New Name',
            'shelve_location_MAIN' => 'NEW-LOC',
            'processed_at' => null,
        ]);

        (new ProcessCsvProductsImportJob($csvUploadedFile->id))->handle();

        $this->assertDatabaseHas('products', [
            'sku' => 'TEST-001',
            'name' => 'New Name',
        ]);

        $this->assertDatabaseHas('inventory', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'shelve_location' => 'NEW-LOC',
        ]);
    }

    #[Test]
    public function it_updates_supplier_code_from_csv()
    {
        $warehouse = Warehouse::factory()->create(['code' => 'MAIN']);
        AddWarehouseColumnsToCsvProductImportTableJob::dispatchSync();

        $product = Product::factory()->create([
            'sku' => 'SUP-001',
            'supplier_code' => 'OLD',
        ]);

        $csvUploadedFile = CsvUploadedFile::create([
            'filename' => 'supplier.csv',
            'mapped_fields' => [
                'sku' => 0,
                'supplier_code' => 1,
            ],
        ]);

        CsvProductImport::factory()->create([
            'file_id' => $csvUploadedFile->id,
            'sku' => 'SUP-001',
            'supplier_code' => 'NEW',
            'processed_at' => null,
        ]);

        (new ProcessCsvProductsImportJob($csvUploadedFile->id))->handle();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'supplier_code' => 'NEW',
        ]);
    }

    #[Test]
    public function it_updates_pack_quantity_from_csv()
    {
        $warehouse = Warehouse::factory()->create(['code' => 'MAIN']);
        AddWarehouseColumnsToCsvProductImportTableJob::dispatchSync();

        $product = Product::factory()->create([
            'sku' => 'PACK-001',
            'pack_quantity' => 1,
        ]);

        $csvUploadedFile = CsvUploadedFile::create([
            'filename' => 'pack.csv',
            'mapped_fields' => [
                'sku' => 0,
                'pack_quantity' => 1,
            ],
        ]);

        CsvProductImport::factory()->create([
            'file_id' => $csvUploadedFile->id,
            'sku' => 'PACK-001',
            'pack_quantity' => 20,
            'processed_at' => null,
        ]);

        (new ProcessCsvProductsImportJob($csvUploadedFile->id))->handle();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'pack_quantity' => 20,
        ]);
    }

    #[Test]
    public function it_removes_tags_using_tags_remove()
    {
        $warehouse = Warehouse::factory()->create(['code' => 'MAIN']);
        AddWarehouseColumnsToCsvProductImportTableJob::dispatchSync();

        $product = Product::factory()->create(['sku' => 'TAG-001']);
        $product->attachTags(['old1', 'old2']);

        $csvUploadedFile = CsvUploadedFile::create([
            'filename' => 'tags.csv',
            'mapped_fields' => [
                'sku' => 0,
                'tags_remove' => 1,
            ],
        ]);

        CsvProductImport::factory()->create([
            'file_id' => $csvUploadedFile->id,
            'sku' => 'TAG-001',
            'tags_add' => null,
            'tags_remove' => 'old2',
            'processed_at' => null,
        ]);

        (new ProcessCsvProductsImportJob($csvUploadedFile->id))->handle();

        $product->refresh();
        $this->assertEqualsCanonicalizing(['old1'], $product->tags->pluck('name')->all());
    }

    #[Test]
    public function it_skips_records_with_missing_sku()
    {
        $csvUploadedFile = CsvUploadedFile::create([
            'filename' => 'invalid.csv',
            'mapped_fields' => [
                'sku' => 0,
                'name' => 1,
            ],
        ]);

        $importRecord = CsvProductImport::factory()->create([
            'file_id' => $csvUploadedFile->id,
            'sku' => null,
            'name' => 'Product With No Sku',
            'processed_at' => null,
        ]);

        (new ProcessCsvProductsImportJob($csvUploadedFile->id))->handle();

        $this->assertDatabaseCount('products', 0);
        $this->assertNull($importRecord->fresh()->processed_at, 'Record should not be processed due to missing SKU');
    }

    #[Test]
    public function it_processes_all_unprocessed_files_when_no_file_id_provided()
    {
        $warehouse = Warehouse::factory()->create(['code' => 'MAIN']);
        AddWarehouseColumnsToCsvProductImportTableJob::dispatchSync();

        // Create multiple unprocessed CSV files
        $csvFile1 = CsvUploadedFile::create([
            'filename' => 'test1.csv',
            'mapped_fields' => ['sku' => 0, 'name' => 1],
            'processed_at' => null,
        ]);

        $csvFile2 = CsvUploadedFile::create([
            'filename' => 'test2.csv',
            'mapped_fields' => ['sku' => 0, 'name' => 1],
            'processed_at' => null,
        ]);

        // Create import records for each file
        CsvProductImport::factory()->create([
            'file_id' => $csvFile1->id,
            'sku' => 'TEST-001',
            'name' => 'Test Product 1',
            'processed_at' => null,
        ]);

        CsvProductImport::factory()->create([
            'file_id' => $csvFile2->id,
            'sku' => 'TEST-002',
            'name' => 'Test Product 2',
            'processed_at' => null,
        ]);

        // Dispatch job without file ID (or with 0)
        $job = new ProcessCsvProductsImportJob(0);
        $result = $job->handle();

        $this->assertTrue($result);

        // Verify both products were created
        $this->assertDatabaseHas('products', ['sku' => 'TEST-001', 'name' => 'Test Product 1']);
        $this->assertDatabaseHas('products', ['sku' => 'TEST-002', 'name' => 'Test Product 2']);

        // Verify import records were marked as processed
        $this->assertNotNull(CsvProductImport::where('sku', 'TEST-001')->first()->processed_at);
        $this->assertNotNull(CsvProductImport::where('sku', 'TEST-002')->first()->processed_at);
    }

    #[Test]
    public function it_only_updates_mapped_fields()
    {
        $warehouse = Warehouse::factory()->create(['code' => 'MAIN']);
        AddWarehouseColumnsToCsvProductImportTableJob::dispatchSync();

        // Create a product with existing data
        $product = Product::factory()->create([
            'sku' => 'PARTIAL-001',
            'name' => 'Original Name',
            'department' => 'Original Department',
            'price' => 100,
            'sale_price' => 80,
        ]);

        // Create CSV upload with only SKU and name mapped
        $csvUploadedFile = CsvUploadedFile::create([
            'filename' => 'partial.csv',
            'mapped_fields' => [
                'sku' => 0,
                'name' => 1,
            ],
        ]);

        // Create import record with different values for all fields
        CsvProductImport::factory()->create([
            'file_id' => $csvUploadedFile->id,
            'sku' => 'PARTIAL-001',
            'name' => 'Updated Name',
            'department' => 'New Department',
            'price' => 200,
            'sale_price' => 150,
            'processed_at' => null,
        ]);

        (new ProcessCsvProductsImportJob($csvUploadedFile->id))->handle();

        $product->refresh();

        // Only name should be updated since only SKU and name were mapped
        $this->assertEquals('Updated Name', $product->name);
        // These fields should remain unchanged
        $this->assertEquals('Original Department', $product->department);
        $this->assertEquals(100, $product->price);
        $this->assertEquals(80, $product->sale_price);
    }
}
