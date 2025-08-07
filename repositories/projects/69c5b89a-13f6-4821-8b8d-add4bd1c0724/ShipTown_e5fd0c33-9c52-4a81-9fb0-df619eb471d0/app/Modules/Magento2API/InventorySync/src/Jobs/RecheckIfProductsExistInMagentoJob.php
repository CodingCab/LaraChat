<?php

namespace App\Modules\Magento2API\InventorySync\src\Jobs;

use App\Abstracts\UniqueJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RecheckIfProductsExistInMagentoJob extends UniqueJob
{
    public function handle(): void
    {
        do {
            $recordsAffected = DB::affectingStatement('
                WITH tempTable AS (
                    SELECT
                      modules_magento2msi_inventory_source_items.id as modules_magento2msi_inventory_source_items_id

                    FROM `modules_magento2msi_inventory_source_items`

                    LEFT JOIN inventory_totals_by_warehouse_tag as inventory_groups
                      ON inventory_groups.id = modules_magento2msi_inventory_source_items.inventory_totals_by_warehouse_tag_id

                    WHERE `exists_in_magento` = 0
                      AND inventory_groups.quantity_available > 0

                    LIMIT 10000
                )

                UPDATE modules_magento2msi_inventory_source_items
                INNER JOIN tempTable ON tempTable.modules_magento2msi_inventory_source_items_id = modules_magento2msi_inventory_source_items.id

                SET modules_magento2msi_inventory_source_items.exists_in_magento = null;
            ');

            Log::info('Job processing', [
                'job' => self::class,
                'recordsAffected' => $recordsAffected,
            ]);

            usleep(100000); // 0.1 second
        } while ($recordsAffected > 0);
    }
}
