<?php

namespace App\Modules\DataCollectorOfflineInventoryReserveStock\src\Listeners;

use App\Events\DataCollectionRecord\DataCollectionRecordUpdatedEvent;
use App\Models\DataCollectionOfflineInventory;
use App\Modules\DataCollectorOfflineInventoryReserveStock\src\Jobs\UpdateStockReservationsJob;

class DataCollectionRecordUpdatedEventListener
{
    public function handle(DataCollectionRecordUpdatedEvent $event): void
    {
        if ($event->dataCollectionRecord->dataCollection->type !== DataCollectionOfflineInventory::class) {
            return;
        }

        UpdateStockReservationsJob::dispatch($event->dataCollectionRecord);
    }
}
