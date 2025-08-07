<?php

namespace App\Modules\Magento2API\InventorySync\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Class SyncCheckFailedProductsJob.
 */
class EnsureProductRecordsExistJob extends UniqueJob
{
    public function handle(): void
    {
        $maxId = DB::table('products')->max('id') ?? 0;

        do {
            $minId = max($maxId - 5000, 0);

            $recordsUpdated = DB::affectingStatement("
                INSERT INTO modules_magento2msi_inventory_source_items (connection_id, product_id, inventory_totals_by_warehouse_tag_id, sku, source_code, custom_uuid, created_at, updated_at)
                SELECT
                        modules_magento2msi_connections.id as connection_id,
                        inventory_totals_by_warehouse_tag.product_id as product_id,
                        inventory_totals_by_warehouse_tag.id as inventory_totals_by_warehouse_tag_id,
                        products.sku as sku,
                        modules_magento2msi_connections.magento_source_code as source_code,
                        CONCAT(products.sku, '-', modules_magento2msi_connections.magento_source_code) as custom_uuid,
                        NOW() as created_at,
                        NOW() as updated_at

                FROM taggables as available_online_tag

                LEFT JOIN products on products.id = available_online_tag.taggable_id

                LEFT JOIN `modules_magento2msi_connections`
                    ON modules_magento2msi_connections.enabled = 1

                INNER JOIN inventory_totals_by_warehouse_tag
                    ON inventory_totals_by_warehouse_tag.tag_id = modules_magento2msi_connections.inventory_source_warehouse_tag_id
                    AND inventory_totals_by_warehouse_tag.product_id = available_online_tag.taggable_id

                LEFT JOIN modules_magento2msi_inventory_source_items
                    ON modules_magento2msi_inventory_source_items.connection_id = modules_magento2msi_connections.id
                    AND modules_magento2msi_inventory_source_items.sku = products.sku

                WHERE available_online_tag.tag_id IN (SELECT ID FROM `tags` WHERE JSON_EXTRACT(name, '$.en') = ?)
                AND available_online_tag.taggable_type = ?
                AND available_online_tag.taggable_id BETWEEN ? AND ?
                AND modules_magento2msi_inventory_source_items.id IS NULL
           ", ['Available Online', Product::class, $minId, $maxId]);

            $maxId = $minId;

            Log::info('Magento2msi - Job processing', [
                'job' => self::class,
                'recordsUpdated' => $recordsUpdated,
                'maxId' => $maxId,
            ]);

            usleep(100000); // 0.1 second
        } while ($maxId > 0);
    }
}
