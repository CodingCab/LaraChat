<?php

namespace App\Modules\InventoryTotals\src\Jobs;

use App\Abstracts\UniqueJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RecalculateProductTotalsTableJob extends UniqueJob
{
    public function handle(): void
    {
        do {
            $recordsUpdated = $this->recalculateTotals();

            Log::debug('Job processing', ['job' => self::class, 'records_updated' => $recordsUpdated]);

            usleep(100000); // 0.1 sec
        } while ($recordsUpdated > 0);
    }
    private function recalculateTotals(): int
    {
        DB::statement('DROP TEMPORARY TABLE IF EXISTS tempTable;');
        DB::statement('DROP TEMPORARY TABLE IF EXISTS tempInventoryTotals;');

        DB::statement('
            CREATE TEMPORARY TABLE tempTable AS
                SELECT
                    products.id as product_id, NOW() as calculated_at
                FROM products

                WHERE recount_required = 1

                LIMIT 500;
        ');

        DB::statement('
            CREATE TEMPORARY TABLE tempInventoryTotals AS
                SELECT
                     tempTable.product_id as product_id,
                     GREATEST(0, FLOOR(SUM(inventory.quantity))) as quantity,
                     GREATEST(0, FLOOR(SUM(inventory.quantity_reserved))) as quantity_reserved,
                     GREATEST(0, FLOOR(SUM(inventory.quantity_available))) as quantity_available,
                     MAX(inventory.updated_at) as max_inventory_updated_at,
                     tempTable.calculated_at as calculated_at,
                     NOW() as created_at,
                     NOW() as updated_at

                FROM tempTable

                LEFT JOIN inventory
                    ON inventory.product_id = tempTable.product_id

                GROUP BY tempTable.product_id, tempTable.calculated_at;
        ');

        return DB::update('
            UPDATE products

            INNER JOIN tempInventoryTotals
                ON tempInventoryTotals.product_id = products.id

            SET
                products.recount_required = 0,
                products.quantity = tempInventoryTotals.quantity,
                products.quantity_reserved = tempInventoryTotals.quantity_reserved,
                products.quantity_available = tempInventoryTotals.quantity_available,
                products.calculated_at = tempInventoryTotals.calculated_at,
                products.updated_at = NOW();
        ');
    }
}
