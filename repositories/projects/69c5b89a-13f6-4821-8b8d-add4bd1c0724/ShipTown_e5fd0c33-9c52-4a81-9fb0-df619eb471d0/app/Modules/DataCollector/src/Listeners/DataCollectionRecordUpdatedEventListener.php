<?php

namespace App\Modules\DataCollector\src\Listeners;

use App\Events\DataCollectionRecord\DataCollectionRecordUpdatedEvent;
use App\Modules\DataCollector\src\Jobs\RecountTotalsJob;

class DataCollectionRecordUpdatedEventListener
{
    public function handle(DataCollectionRecordUpdatedEvent $event): void
    {
        RecountTotalsJob::dispatch($event->dataCollectionRecord->data_collection_id);
    }
}
