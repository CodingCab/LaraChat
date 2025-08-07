<?php

namespace App\Modules\DataCollectorTransfersOutReserveStock\src\Listeners;

use App\Events\DataCollection\DataCollectionUpdatedEvent;
use App\Modules\DataCollectorTransfersOutReserveStock\src\Jobs\CheckReservationsDataIntegrityJob;

class DataCollectionUpdatedEventListener
{
    public function handle(DataCollectionUpdatedEvent $event): void
    {
        CheckReservationsDataIntegrityJob::dispatch($event->dataCollection->getKey());
    }
}
