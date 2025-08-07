<?php

namespace App\Modules\DataCollectorOfflineInventoryReserveStock\src\Listeners;

use App\Events\DataCollectionRecord\DataCollectionRecordDeletedEvent;
use App\Models\DataCollectionOfflineInventory;
use App\Modules\DataCollectorOfflineInventoryReserveStock\src\Jobs\UpdateStockReservationsJob;

class DataCollectionRecordDeletedEventListener
{
    public function handle(DataCollectionRecordDeletedEvent $event): void
    {
        if ($event->dataCollectionRecord->dataCollection->type !== DataCollectionOfflineInventory::class) {
            return;
        }

        UpdateStockReservationsJob::dispatch($event->dataCollectionRecord);
    }
}
