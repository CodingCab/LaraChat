<?php

namespace App\Modules\DataCollectorTransfersOutReserveStock\src\Listeners;

use App\Events\DataCollection\DataCollectionDeletedEvent;
use App\Modules\DataCollectorTransfersOutReserveStock\src\Jobs\CheckReservationsDataIntegrityJob;
use App\Modules\Inventory\src\Jobs\RecalculateInventoryRecordsJob;

class DataCollectionDeletedEventListener
{
    public function handle(DataCollectionDeletedEvent $event): void
    {
        CheckReservationsDataIntegrityJob::dispatch($event->dataCollection->getKey());
        RecalculateInventoryRecordsJob::dispatch();
    }
}
