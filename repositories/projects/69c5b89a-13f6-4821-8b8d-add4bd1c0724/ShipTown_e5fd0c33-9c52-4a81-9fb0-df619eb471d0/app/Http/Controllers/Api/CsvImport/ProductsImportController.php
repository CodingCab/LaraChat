<?php

namespace App\Http\Controllers\Api\CsvImport;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ProductsImport\StoreRequest;
use App\Modules\CsvProductImports\src\Jobs\ProcessCsvUploadedFileJob;
use App\Modules\CsvProductImports\src\Models\CsvUploadedFile;
use App\Modules\Reports\src\Models\CsvProductImportReport;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductsImportController extends Controller
{
    public function index(): JsonResource
    {
        return CsvProductImportReport::json();
    }

    public function store(StoreRequest $request): JsonResource
    {
        $fields = $request->validated();

        $file = $request->file('file');
        $filename = $file->getClientOriginalName();
        $fileContent = $file->getContent();
        $mappedFields = json_decode($fields['mappedFields'], true);

        $uploadedFile = CsvUploadedFile::query()->create([
            'filename' => $filename,
            'file_content' => $fileContent,
            'mapped_fields' => $mappedFields,
        ]);

        $uploadedFile->log('CSV file uploaded', [
            'filename' => $filename,
            'mapped_fields' => $mappedFields,
        ]);

        ProcessCsvUploadedFileJob::dispatchAfterResponse($uploadedFile);

        return JsonResource::make([
            'success' => true,
            'message' => t('CSV import has been queued for processing.'),
        ]);
    }
}
