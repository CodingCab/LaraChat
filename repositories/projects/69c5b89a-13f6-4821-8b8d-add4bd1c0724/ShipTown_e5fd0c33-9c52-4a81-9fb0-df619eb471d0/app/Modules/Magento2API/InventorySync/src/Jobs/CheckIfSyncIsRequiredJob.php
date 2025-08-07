<?php

namespace App\Modules\Magento2API\InventorySync\src\Jobs;

use App\Abstracts\UniqueJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckIfSyncIsRequiredJob extends UniqueJob
{
    public function handle(): void
    {
        do {
            $recordsAffected = DB::affectingStatement('
                WITH tempTable AS (
                        SELECT modules_magento2msi_inventory_source_items.id

                        FROM modules_magento2msi_inventory_source_items

                        WHERE modules_magento2msi_inventory_source_items.sync_required IS NULL
                            AND modules_magento2msi_inventory_source_items.quantity IS NOT NULL

                        LIMIT 500
                )

                UPDATE modules_magento2msi_inventory_source_items

                INNER JOIN tempTable ON tempTable.id = modules_magento2msi_inventory_source_items.id

                LEFT JOIN inventory_totals_by_warehouse_tag
                  ON inventory_totals_by_warehouse_tag.id = modules_magento2msi_inventory_source_items.inventory_totals_by_warehouse_tag_id

                SET modules_magento2msi_inventory_source_items.sync_required = (modules_magento2msi_inventory_source_items.quantity != inventory_totals_by_warehouse_tag.quantity_available),
                    modules_magento2msi_inventory_source_items.updated_at = NOW()
           ');

            usleep(100000); // 0.1 second
            Log::info('Magento2msi - Job processing', [
                'job' => self::class,
                'recordsAffected' => $recordsAffected,
            ]);
        } while ($recordsAffected > 0);
    }
}
