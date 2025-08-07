<?php

namespace App\Modules\CsvProductImports\src\Listeners;

use App\Modules\CsvProductImports\src\Jobs\ProcessCsvProductsImportJob;
use App\Modules\CsvProductImports\src\Jobs\ProcessCsvUploadedFileJob;
use App\Modules\CsvProductImports\src\Models\CsvUploadedFile;

class EveryMinuteEventListener
{
    public function handle(): void
    {
        CsvUploadedFile::query()->whereNull('processed_at')
            ->chunkByIdDesc(10, function ($csvUploadedFiles) {
                foreach ($csvUploadedFiles as $csvUploadedFile) {
                    ProcessCsvUploadedFileJob::dispatch($csvUploadedFile);
                }
            });

        ProcessCsvProductsImportJob::dispatch();
    }
}
