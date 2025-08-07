<?php

namespace App\Modules\Rmsapi\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Modules\Rmsapi\src\Models\RmsapiSaleImport;
use Illuminate\Support\Facades\DB;

class UpdateImportedSalesRecordsJob extends UniqueJob
{
    public function handle(): bool
    {
        $this->updateWarehouseIds();

        $this->updateProductIds();

        $this->updateInventoryIds();

        $this->releaseTimedoutSalesRecords();

        return true;
    }

    private function updateWarehouseIds(): void
    {
        DB::statement('
            UPDATE modules_rmsapi_sales_imports
            LEFT JOIN modules_rmsapi_connections
              ON modules_rmsapi_connections.id = modules_rmsapi_sales_imports.connection_id

            SET modules_rmsapi_sales_imports.warehouse_id = modules_rmsapi_connections.warehouse_id

            WHERE modules_rmsapi_sales_imports.warehouse_id IS NULL
        ');
    }

    private function updateProductIds(): void
    {
        DB::statement('
            UPDATE modules_rmsapi_sales_imports
            LEFT JOIN products_aliases
              ON modules_rmsapi_sales_imports.sku = products_aliases.alias

            SET modules_rmsapi_sales_imports.product_id = products_aliases.product_id

            WHERE modules_rmsapi_sales_imports.product_id IS NULL
        ');
    }

    private function releaseTimedoutSalesRecords(): void
    {
        RmsapiSaleImport::query()
            ->whereNull('processed_at')
            ->where('reserved_at', '<', now()->subMinutes(5))
            ->update(['reserved_at' => null]);
    }

    private function updateInventoryIds()
    {
        DB::statement('
            UPDATE modules_rmsapi_sales_imports
            LEFT JOIN inventory
              ON modules_rmsapi_sales_imports.product_id = inventory.product_id
              AND modules_rmsapi_sales_imports.warehouse_id = inventory.warehouse_id

            SET modules_rmsapi_sales_imports.inventory_id = inventory.id

            WHERE modules_rmsapi_sales_imports.inventory_id IS NULL
        ');
    }
}
