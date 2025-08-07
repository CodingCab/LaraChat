<?php

namespace Tests\Modules\CsvProductImports\src\Jobs;

use App\Modules\CsvProductImports\src\Jobs\ProcessCsvUploadedFileJob;
use App\Modules\CsvProductImports\src\Models\CsvProductImport;
use App\Modules\CsvProductImports\src\Models\CsvUploadedFile;
use Tests\TestCase;

class ProcessCsvUploadedFileJobUpsertTest extends TestCase
{
    public function test_job_uses_upsert_to_handle_duplicate_processing()
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

        // Process the file once
        (new ProcessCsvUploadedFileJob($csvUploadedFile))->handle();

        // Verify records were created with line numbers
        $this->assertEquals(2, CsvProductImport::where('file_id', $csvUploadedFile->id)->count());

        $record1 = CsvProductImport::where('file_id', $csvUploadedFile->id)
            ->where('sku', 'TEST001')
            ->first();
        $this->assertEquals(2, $record1->line_number); // Line 2 (header is line 1)

        $record2 = CsvProductImport::where('file_id', $csvUploadedFile->id)
            ->where('sku', 'TEST002')
            ->first();
        $this->assertEquals(3, $record2->line_number); // Line 3

        // Update the CSV content with modified prices
        $csvUploadedFile->update([
            'file_content' => "sku;name;price\n" .
                "TEST001;Test Product 1;150.00\n" .
                "TEST002;Test Product 2;250.00",
            'processed_at' => null,
            'processed_records' => 0,
            'invalid_records' => 0
        ]);

        // Process the file again
        (new ProcessCsvUploadedFileJob($csvUploadedFile))->handle();

        // Verify we still have only 2 records (not 4)
        $this->assertEquals(2, CsvProductImport::where('file_id', $csvUploadedFile->id)->count());

        // Verify the prices were updated
        $record1->refresh();
        $this->assertEquals(150.00, (float)$record1->price);

        $record2->refresh();
        $this->assertEquals(250.00, (float)$record2->price);
    }

    public function test_job_tracks_line_numbers_correctly()
    {
        $csvContent = "sku;name;price\n" .
            "TEST001;Product 1;10.00\n" .
            "TEST002\n" .  // Invalid line (line 3)
            ";Product 3;30.00\n" . // Invalid line (line 4)
            "TEST004;Product 4;40.00"; // Valid line (line 5)

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

        // Check that only valid records were created with correct line numbers
        $records = CsvProductImport::where('file_id', $csvUploadedFile->id)
            ->orderBy('line_number')
            ->get();

        $this->assertEquals(2, $records->count());

        $this->assertEquals('TEST001', $records[0]->sku);
        $this->assertEquals(2, $records[0]->line_number);

        $this->assertEquals('TEST004', $records[1]->sku);
        $this->assertEquals(5, $records[1]->line_number);
    }
}
