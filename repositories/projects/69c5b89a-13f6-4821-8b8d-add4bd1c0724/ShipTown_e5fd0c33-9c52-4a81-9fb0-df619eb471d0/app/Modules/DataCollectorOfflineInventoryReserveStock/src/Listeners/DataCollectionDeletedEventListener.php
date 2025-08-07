<?php

namespace App\Modules\DataCollectorOfflineInventoryReserveStock\src\Listeners;

use App\Events\DataCollection\DataCollectionDeletedEvent;
use App\Models\DataCollectionOfflineInventory;
use App\Modules\DataCollectorOfflineInventoryReserveStock\src\Jobs\DeleteStockReservationsJob;

class DataCollectionDeletedEventListener
{
    public function handle(DataCollectionDeletedEvent $event): void
    {
        if ($event->dataCollection->type !== DataCollectionOfflineInventory::class) {
            return;
        }

        DeleteStockReservationsJob::dispatch($event->dataCollection);
    }
}
