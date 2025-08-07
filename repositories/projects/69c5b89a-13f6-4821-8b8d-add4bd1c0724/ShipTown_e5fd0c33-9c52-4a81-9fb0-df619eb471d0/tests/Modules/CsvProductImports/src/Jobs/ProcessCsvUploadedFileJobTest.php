<?php

namespace Tests\Modules\CsvProductImports\src\Jobs;

use App\Modules\CsvProductImports\src\Models\CsvProductImport;
use Exception;
use Tests\TestCase;
use Illuminate\Support\Facades\Bus;
use App\Modules\CsvProductImports\src\Models\CsvUploadedFile;
use App\Modules\CsvProductImports\src\Jobs\ProcessCsvUploadedFileJob;

class ProcessCsvUploadedFileJobTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Bus::fake();
    }

    public function test_job_updates_status_on_successful_processing()
    {
        $csvContent = "sku;name;price\n" .
            "TEST001;Test Product 1;100.00\n" .
            "TEST002;Test Product 2;200.00";

        $mappedFields = [
            'sku' => 0,
            'name' => 1,
            'price' => 2,
        ];

        /** @var CsvUploadedFile $csvUploadedFile */
        $csvUploadedFile = CsvUploadedFile::query()->create([
            'filename' => 'test_import.csv',
            'file_content' => $csvContent,
            'mapped_fields' => $mappedFields
        ]);

        (new ProcessCsvUploadedFileJob($csvUploadedFile))->handle();

        $csvUploadedFile->refresh();
        $this->assertNotNull($csvUploadedFile->processed_at);
        $this->assertEquals(2, $csvUploadedFile->processed_records);
        $this->assertEquals(0, $csvUploadedFile->invalid_records);
    }

    public function test_job_updates_status_with_valid_and_invalid_records()
    {
        $csvContent = "sku;name;price\n" .
            "TEST001;Product 1;10.00\n" .
            "TEST002\n" .
            ";Product 3;30.00\n" .
            "TEST004;;40.00";

        $mappedFields = [
            'sku' => 0,
            'name' => 1,
            'price' => 2,
        ];

        /** @var CsvUploadedFile $csvUploadedFile */
        $csvUploadedFile = CsvUploadedFile::query()->create([
            'filename' => 'test_import.csv',
            'file_content' => $csvContent,
            'mapped_fields' => $mappedFields,
        ]);

        (new ProcessCsvUploadedFileJob($csvUploadedFile))->handle();

        $csvUploadedFile->refresh();
        $this->assertNotNull($csvUploadedFile->processed_at);
        $this->assertEquals(2, $csvUploadedFile->processed_records);
        $this->assertEquals(2, $csvUploadedFile->invalid_records);
    }

    public function test_job_updates_status_and_throws_exception_for_missing_mappings()
    {
        /** @var CsvUploadedFile $csvUploadedFile */
        $csvUploadedFile = CsvUploadedFile::query()->create([
            'filename' => 'test_import.csv',
            'file_content' => "sku;name\nTEST001;Product1",
            'mapped_fields' => null,
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid field mappings');

        try {
            (new ProcessCsvUploadedFileJob($csvUploadedFile))->handle();
        } finally {
            $csvUploadedFile->refresh();
            $this->assertNotNull($csvUploadedFile->processed_at);
            $this->assertEquals(0, $csvUploadedFile->processed_records);
            $this->assertEquals(0, $csvUploadedFile->invalid_records);
        }
    }

    public function test_job_handles_empty_file_content_gracefully()
    {
        $mappedFields = [
            'sku' => 0,
            'name' => 1,
        ];

        /** @var CsvUploadedFile $csvUploadedFile */
        $csvUploadedFile = CsvUploadedFile::query()->create([
            'filename' => 'test_import.csv',
            'file_content' => '',
            'mapped_fields' => $mappedFields,
        ]);

        (new ProcessCsvUploadedFileJob($csvUploadedFile))->handle();

        $csvUploadedFile->refresh();
        $this->assertNotNull($csvUploadedFile->processed_at);
        $this->assertEquals(0, $csvUploadedFile->processed_records);
        $this->assertEquals(0, $csvUploadedFile->invalid_records);
    }

    public function test_job_handles_file_with_only_header_gracefully()
    {
        $csvContent = "sku;name;price";

        $mappedFields = [
            'sku' => 0,
            'name' => 1,
            'price' => 2,
        ];

        /** @var CsvUploadedFile $csvUploadedFile */
        $csvUploadedFile = CsvUploadedFile::query()->create([
            'filename' => 'test_import.csv',
            'file_content' => $csvContent,
            'mapped_fields' => $mappedFields,
        ]);

        (new ProcessCsvUploadedFileJob($csvUploadedFile))->handle();

        $csvUploadedFile->refresh();
        $this->assertNotNull($csvUploadedFile->processed_at);
        $this->assertEquals(0, $csvUploadedFile->processed_records);
        $this->assertEquals(0, $csvUploadedFile->invalid_records);
    }

    public function test_job_handles_large_csv_files_efficiently()
    {
        // Generate a large CSV with 2,000 rows
        $csvContent = "sku,name,price,department,category,weight\n";
        $batchSize = 5000;

        for ($i = 1; $i <= $batchSize; $i++) {
            $csvContent .= sprintf(
                "SKU%06d,Product %d,%.2f,Dept%d,Cat%d,%.2f\n",
                $i,
                $i,
                rand(100, 99999) / 100,
                rand(1, 10),
                rand(1, 50),
                rand(100, 5000) / 100
            );
        }

        $mappedFields = [
            'sku' => 0,
            'name' => 1,
            'price' => 2,
            'department' => 3,
            'category' => 4,
            'weight' => 5,
        ];

        /** @var CsvUploadedFile $csvUploadedFile */
        $csvUploadedFile = CsvUploadedFile::query()->create([
            'filename' => 'large_test_import.csv',
            'file_content' => $csvContent,
            'mapped_fields' => $mappedFields
        ]);

        $startTime = microtime(true);
        (new ProcessCsvUploadedFileJob($csvUploadedFile))->handle();
        $executionTime = microtime(true) - $startTime;

        $csvUploadedFile->refresh();

        // Assertions
        $this->assertNotNull($csvUploadedFile->processed_at);
        $this->assertEquals($batchSize, $csvUploadedFile->processed_records);
        $this->assertEquals(0, $csvUploadedFile->invalid_records);

        // Verify records were inserted in batches
        $recordCount = CsvProductImport::query()->where('file_id', $csvUploadedFile->id)->count();
        $this->assertEquals($batchSize, $recordCount);

        // Performance assertion - should process 2k records in under 5 seconds
        $this->assertLessThan(5, $executionTime, 'Processing 2,000 records took too long');
    }

}
