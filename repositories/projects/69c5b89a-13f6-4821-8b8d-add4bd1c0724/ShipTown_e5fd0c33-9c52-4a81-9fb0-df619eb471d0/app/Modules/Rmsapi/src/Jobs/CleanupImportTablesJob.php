<?php

namespace App\Modules\Rmsapi\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Modules\Rmsapi\src\Models\RmsapiProductImport;
use App\Modules\Rmsapi\src\Models\RmsapiSaleImport;
use App\Modules\Rmsapi\src\Models\RmsapiShippingImports;
use Illuminate\Support\Facades\DB;

class CleanupImportTablesJob extends UniqueJob
{
    public function handle(): bool
    {
        // remove duplicates from products import table, keep the latest only
        DB::statement('
            DELETE FROM modules_rmsapi_products_imports WHERE ID NOT IN (
                SELECT id FROM (
                    SELECT max(id) as id
                    FROM `modules_rmsapi_products_imports`
                    GROUP BY product_id, warehouse_id
                ) as tbl)
        ');

        // Cleanup products import table
        RmsapiProductImport::query()
            ->whereNull('processed_at')
            ->where('reserved_at', '<', now()->subMinutes(5))
            ->update(['reserved_at' => null]);

        // Cleanup sales imports
        RmsapiSaleImport::query()
            ->whereNull('processed_at')
            ->where('reserved_at', '<', now()->subMinutes(5))
            ->update(['reserved_at' => null]);

        RmsapiSaleImport::query()
            ->where('processed_at', '<', now()->subDays(7))
            ->forceDelete();

        // Cleanup shipping imports
        RmsapiShippingImports::query()
            ->where('created_at', '<', now()->subDays(7))
            ->forceDelete();

        return true;
    }
}
