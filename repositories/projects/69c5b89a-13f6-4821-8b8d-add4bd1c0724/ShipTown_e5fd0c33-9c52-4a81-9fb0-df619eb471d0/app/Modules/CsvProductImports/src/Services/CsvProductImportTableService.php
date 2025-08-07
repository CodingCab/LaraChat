<?php

namespace App\Modules\CsvProductImports\src\Services;

use App\Models\Warehouse;
use App\Modules\CsvProductImports\src\Jobs\AddWarehouseColumnsToCsvProductImportTableJob;
use App\Modules\CsvProductImports\src\Jobs\RemoveWarehouseColumnsFromCsvProductImportTableJob;
use App\Modules\CsvProductImports\src\Jobs\UpdateWarehouseColumnsInCsvProductImportTableJob;

class CsvProductImportTableService
{
    public static function dispatchAddColumnsJob(Warehouse $warehouse): void
    {
        AddWarehouseColumnsToCsvProductImportTableJob::dispatch();
    }

    public static function dispatchRemoveColumnsJob(Warehouse $warehouse): void
    {
        RemoveWarehouseColumnsFromCsvProductImportTableJob::dispatch($warehouse);
    }

    public static function dispatchUpdateColumnsJob(Warehouse $warehouse, string $oldCode): void
    {
        UpdateWarehouseColumnsInCsvProductImportTableJob::dispatch($warehouse, $oldCode);
    }
}
