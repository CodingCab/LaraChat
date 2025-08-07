<?php

namespace App\Modules\InventoryQuantityIncoming\src\Listeners;

use App\Events\Inventory\RecalculateInventoryRequestEvent;
use App\Models\Inventory;
use App\Modules\InventoryQuantityIncoming\src\Jobs\RecalculateInventoryQuantityIncomingJob;

class RecalculateInventoryRequestEventListener
{
    public function handle(RecalculateInventoryRequestEvent $event): void
    {
        $event->inventory->each(function (Inventory $inventory) {
            RecalculateInventoryQuantityIncomingJob::dispatch($inventory->product_id, $inventory->warehouse_id);
        });
    }
}
