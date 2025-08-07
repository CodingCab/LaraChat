<?php

namespace App\Modules\Magento2API\InventorySync\src\Listeners;

use App\Events\Inventory\RecalculateInventoryRequestEvent;
use Illuminate\Support\Facades\DB;

class RecalculateInventoryRequestEventListener
{
    public function handle(RecalculateInventoryRequestEvent $event): void
    {
        DB::affectingStatement('
            UPDATE modules_magento2msi_inventory_source_items
            SET
                modules_magento2msi_inventory_source_items.sync_required = null,
                modules_magento2msi_inventory_source_items.inventory_source_items_fetched_at = null
            WHERE
                modules_magento2msi_inventory_source_items.product_id IN (
                    SELECT DISTINCT product_id FROM inventory WHERE id IN (
                    '.$event->inventoryRecordsIds->implode(',').'
                ))
        ');
    }
}
