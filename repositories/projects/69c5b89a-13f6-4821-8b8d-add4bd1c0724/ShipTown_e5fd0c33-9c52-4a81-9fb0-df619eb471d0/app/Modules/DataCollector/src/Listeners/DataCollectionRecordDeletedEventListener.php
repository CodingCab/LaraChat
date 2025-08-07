<?php

namespace App\Modules\DataCollector\src\Listeners;

use App\Events\DataCollectionRecord\DataCollectionRecordDeletedEvent;
use App\Modules\DataCollector\src\Jobs\RecountTotalsJob;

class DataCollectionRecordDeletedEventListener
{
    public function handle(DataCollectionRecordDeletedEvent $event): void
    {
        RecountTotalsJob::dispatch($event->dataCollectionRecord->data_collection_id);
    }
}
