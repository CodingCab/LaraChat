<?php

namespace App\Modules\DataCollector\src\Listeners;

use App\Events\DataCollectionRecord\DataCollectionRecordCreatedEvent;
use App\Modules\DataCollector\src\Jobs\RecountTotalsJob;

class DataCollectionRecordCreatedEventListener
{
    public function handle(DataCollectionRecordCreatedEvent $event): void
    {
        RecountTotalsJob::dispatch($event->dataCollectionRecord->data_collection_id);
    }
}
