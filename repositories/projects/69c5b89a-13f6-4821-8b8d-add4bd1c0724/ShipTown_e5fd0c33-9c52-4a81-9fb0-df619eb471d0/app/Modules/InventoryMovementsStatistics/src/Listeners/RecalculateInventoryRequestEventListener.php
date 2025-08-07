<?php

namespace App\Modules\InventoryMovementsStatistics\src\Listeners;

use App\Events\Inventory\RecalculateInventoryRequestEvent;
use App\Modules\InventoryMovementsStatistics\src\Services\InventoryMovementsStatisticsService;

class RecalculateInventoryRequestEventListener
{
    public function handle(RecalculateInventoryRequestEvent $event): void
    {
        InventoryMovementsStatisticsService::recalculateInventoryStatistics($event->inventoryRecordsIds);
    }
}
