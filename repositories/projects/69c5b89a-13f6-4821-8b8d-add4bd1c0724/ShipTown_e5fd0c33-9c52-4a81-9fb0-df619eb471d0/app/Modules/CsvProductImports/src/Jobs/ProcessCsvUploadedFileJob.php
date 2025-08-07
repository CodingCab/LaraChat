<?php

namespace App\Modules\CsvProductImports\src\Jobs;

use App\Modules\CsvProductImports\src\Models\CsvProductImport;
use App\Modules\CsvProductImports\src\Models\CsvUploadedFile;
use Exception;
use App\Abstracts\UniqueJob;

class ProcessCsvUploadedFileJob extends UniqueJob
{
    protected CsvUploadedFile $csvUploadedFile;

    public function __construct(CsvUploadedFile $csvUploadedFile)
    {
        $this->csvUploadedFile = $csvUploadedFile;
    }

    public function uniqueId(): string
    {
        return implode('-', [get_class($this), $this->csvUploadedFile->id]);
    }

    public function handle(): void
    {
        if (empty($this->csvUploadedFile->mapped_fields)) {
            $this->csvUploadedFile->update([
                'processed_at' => now(),
                'processed_records' => 0,
                'invalid_records' => 0,
            ]);
            throw new Exception('Invalid field mappings');
        }

        $processedRecords = 0;
        $invalidRecords = 0;

        if (!empty($this->csvUploadedFile->file_content)) {
            $lines = explode("\n", trim($this->csvUploadedFile->file_content));

            if (count($lines) > 1) {
                $delimiter = $this->detectDelimiter($lines[0]);
                $mappedFields = $this->csvUploadedFile->mapped_fields;
                $recordsToUpsert = [];

                for ($i = 1; $i < count($lines); $i++) {
                    $line = trim($lines[$i]);
                    if (empty($line)) {
                        continue;
                    }

                    $values = str_getcsv($line, $delimiter);

                    // Check if row has expected number of columns
                    $expectedColumns = count($mappedFields);
                    $actualColumns = count($values);

                    // If the row doesn't have enough columns, it's invalid
                    if ($actualColumns < $expectedColumns) {
                        $invalidRecords++;
                        continue;
                    }

                    $recordData = [
                        'file_id' => $this->csvUploadedFile->id,
                        'line_number' => $i + 1, // CSV line numbers are 1-based (header is line 1)
                    ];

                    $isValid = true;

                    foreach ($mappedFields as $field => $index) {
                        if (isset($values[$index])) {
                            $value = trim($values[$index]);
                            $recordData[$field] = $value ?: null;
                        } else {
                            $recordData[$field] = null;
                        }
                    }

                    // SKU must be present and not empty
                    if (empty($recordData['sku'])) {
                        $isValid = false;
                    }

                    if ($isValid) {
                        $recordData['created_at'] = now();
                        $recordData['updated_at'] = now();
                        $recordsToUpsert[] = $recordData;
                        $processedRecords++;
                    } else {
                        $invalidRecords++;
                    }

                    // Update progress every 1000 records
                    if ($i % 1000 === 0) {
                        $this->csvUploadedFile->update([
                            'processed_records' => $processedRecords,
                            'invalid_records' => $invalidRecords,
                        ]);
                    }

                    if (count($recordsToUpsert) >= 1000) {
                        // Batch upsert to avoid too many queries
                        CsvProductImport::query()->upsert(
                            $recordsToUpsert,
                            ['file_id', 'line_number'], // unique keys
                            array_keys($recordsToUpsert[0]) // columns to update
                        );
                        $recordsToUpsert = []; // Reset for next batch
                    }
                }

                // Batch upsert all valid records
                if (!empty($recordsToUpsert)) {
                    CsvProductImport::query()->upsert(
                        $recordsToUpsert,
                        ['file_id', 'line_number'], // unique keys
                        array_keys($recordsToUpsert[0]) // columns to update
                    );
                }
            }
        }

        $this->csvUploadedFile->update([
            'processed_at' => now(),
            'processed_records' => $processedRecords,
            'invalid_records' => $invalidRecords,
        ]);

        // Log activity
        activity()
            ->performedOn($this->csvUploadedFile)
            ->withProperties([
                'processed_records' => $processedRecords,
                'invalid_records' => $invalidRecords,
            ])
            ->log("CSV processing completed with {$processedRecords} valid and {$invalidRecords} invalid records");

        if ($processedRecords > 0) {
            ProcessCsvProductsImportJob::dispatch($this->csvUploadedFile->id);
        }
    }

    private function detectDelimiter(string $headerLine): string
    {
        $delimiters = [';', ',', "\t", '|'];
        $counts = [];

        foreach ($delimiters as $delimiter) {
            $counts[$delimiter] = substr_count($headerLine, $delimiter);
        }

        $maxCount = max($counts);
        foreach ($counts as $delimiter => $count) {
            if ($count === $maxCount && $count > 0) {
                return $delimiter;
            }
        }

        return ';';
    }
}
