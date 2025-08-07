<?php

namespace Tests\Modules\CsvProductImports\src\Jobs;

use App\Models\Product;
use App\Modules\CsvProductImports\src\Jobs\ProcessCsvProductsImportJob;
use App\Modules\CsvProductImports\src\Models\CsvProductImport;
use App\Modules\CsvProductImports\src\Models\CsvUploadedFile;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProcessCsvProductsImportJobSqlTest extends TestCase
{
    #[Test]
    public function it_correctly_handles_sql_in_clause_with_multiple_ids()
    {
        // Create test products (aliases will be created automatically)
        $products = [];
        for ($i = 1; $i <= 10; $i++) {
            $product = Product::factory()->create(['sku' => "TEST-SKU-$i"]);
            // Trigger the observer to create alias
            $product->update(['sku' => "TEST-SKU-$i"]);
            $products[] = $product;
        }

        $csvUploadedFile = CsvUploadedFile::create([
            'filename' => 'test.csv',
            'mapped_fields' => [
                'sku' => 0,
                'name' => 1,
            ],
        ]);

        // Create 10 CSV import records without product_id
        $importRecords = [];
        for ($i = 1; $i <= 10; $i++) {
            $importRecords[] = CsvProductImport::factory()->create([
                'file_id' => $csvUploadedFile->id,
                'sku' => "TEST-SKU-$i",
                'name' => "Test Product $i",
                'processed_at' => null,
                'product_id' => null,
            ]);
        }

        // Run the job
        (new ProcessCsvProductsImportJob($csvUploadedFile->id))->handle();

        // Verify all records have been updated with product_id
        foreach ($importRecords as $index => $record) {
            $updatedRecord = $record->fresh();
            $this->assertNotNull($updatedRecord->product_id);
            $this->assertEquals($products[$index]->id, $updatedRecord->product_id);
            $this->assertNotNull($updatedRecord->processed_at);
        }
    }

    #[Test]
    public function it_handles_empty_collection_in_sql_in_clause()
    {
        $csvUploadedFile = CsvUploadedFile::create([
            'filename' => 'test.csv',
            'mapped_fields' => [
                'sku' => 0,
                'name' => 1,
            ],
        ]);

        // Create CSV import records with non-existent SKUs
        CsvProductImport::factory()->create([
            'file_id' => $csvUploadedFile->id,
            'sku' => 'NON-EXISTENT-SKU',
            'name' => 'Test Product',
            'processed_at' => null,
            'product_id' => null,
        ]);

        // Run the job - should not throw SQL error even with no matching products
        (new ProcessCsvProductsImportJob($csvUploadedFile->id))->handle();

        // Verify the record has been processed and a new product was created
        $record = CsvProductImport::where('file_id', $csvUploadedFile->id)->first();
        $this->assertNotNull($record->product_id); // Product should be created
        $this->assertNotNull($record->processed_at);

        // Verify a new product was created with the SKU
        $product = Product::where('sku', 'NON-EXISTENT-SKU')->first();
        $this->assertNotNull($product);
        $this->assertEquals($record->product_id, $product->id);
    }
}
