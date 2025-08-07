<?php

namespace App\Modules\Maintenance\src\Jobs\Products;

use App\Abstracts\UniqueJob;
use Illuminate\Support\Facades\DB;

class EnsureAllInventoryRecordsExistsJob extends UniqueJob
{
    public function handle(): void
    {
        do {
            $recordsAffected = DB::affectingStatement('
                INSERT INTO inventory (
                    product_id,
                    product_sku,
                    warehouse_id,
                    warehouse_code,
                    created_at,
                    updated_at
                )
                SELECT
                    products.id as product_id,
                    products.sku as product_sku,
                    warehouses.id as warehouse_id,
                    warehouses.code as warehouse_code,
                    now(),
                    now()

                FROM products
                LEFT JOIN warehouses ON 1 = 1
                WHERE NOT EXISTS (
                    SELECT product_id FROM inventory
                    WHERE inventory.warehouse_id = warehouses.id
                      AND inventory.product_id = products.id
                )
            ');

            usleep(200000); // 200ms
        } while ($recordsAffected > 0);
    }
}
