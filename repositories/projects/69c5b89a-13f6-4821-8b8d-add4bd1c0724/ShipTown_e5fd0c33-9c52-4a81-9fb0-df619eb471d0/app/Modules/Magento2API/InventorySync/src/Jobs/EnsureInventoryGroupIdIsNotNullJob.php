<?php

namespace App\Modules\Magento2API\InventorySync\src\Jobs;

use App\Abstracts\UniqueJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EnsureInventoryGroupIdIsNotNullJob extends UniqueJob
{
    public function handle(): void
    {
        do {
            $recordsAffected = DB::affectingStatement('
                WITH tempTable AS (
                    SELECT
                      magento_inventory.id as modules_magento2msi_inventory_source_items_id
                      , inventory_groups.id as inventory_totals_by_warehouse_tag_id
                      , inventory_groups.product_id as product_id

                    FROM modules_magento2msi_inventory_source_items as magento_inventory

                    LEFT JOIN modules_magento2msi_connections as connection
                      ON connection.id = magento_inventory.connection_id

                    INNER JOIN inventory_totals_by_warehouse_tag as inventory_groups
                      ON inventory_groups.tag_id = connection.inventory_source_warehouse_tag_id
                      AND inventory_groups.product_sku = magento_inventory.sku

                    WHERE inventory_totals_by_warehouse_tag_id IS NULL

                    LIMIT 1000
                )

                UPDATE modules_magento2msi_inventory_source_items

                INNER JOIN tempTable
                  ON modules_magento2msi_inventory_source_items.id = tempTable.modules_magento2msi_inventory_source_items_id

                SET modules_magento2msi_inventory_source_items.inventory_totals_by_warehouse_tag_id = tempTable.inventory_totals_by_warehouse_tag_id
                , modules_magento2msi_inventory_source_items.product_id = tempTable.product_id
            ');

            Log::info('Magento2msi - Job processing', [
                'job' => self::class,
                'recordsAffected' => $recordsAffected,
            ]);

            usleep(100000); // 0.1 second
        } while ($recordsAffected > 0);
    }
}
