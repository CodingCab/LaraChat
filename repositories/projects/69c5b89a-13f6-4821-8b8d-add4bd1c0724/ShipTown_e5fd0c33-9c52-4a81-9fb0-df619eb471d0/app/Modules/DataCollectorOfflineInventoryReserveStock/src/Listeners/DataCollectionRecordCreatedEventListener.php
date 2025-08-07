<?php

namespace App\Modules\DataCollectorOfflineInventoryReserveStock\src\Listeners;

use App\Events\DataCollectionRecord\DataCollectionRecordCreatedEvent;
use App\Models\DataCollectionOfflineInventory;
use App\Modules\DataCollectorOfflineInventoryReserveStock\src\Jobs\UpdateStockReservationsJob;

class DataCollectionRecordCreatedEventListener
{
    public function handle(DataCollectionRecordCreatedEvent $event): void
    {
        if ($event->dataCollectionRecord->dataCollection->type !== DataCollectionOfflineInventory::class) {
            return;
        }

        UpdateStockReservationsJob::dispatch($event->dataCollectionRecord);
    }
}
