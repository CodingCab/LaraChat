<?php

namespace App\Observers;

use App\Events\DataCollectionRecord\DataCollectionRecordCreatedEvent;
use App\Events\DataCollectionRecord\DataCollectionRecordDeletedEvent;
use App\Events\DataCollectionRecord\DataCollectionRecordUpdatedEvent;
use App\Models\DataCollectionRecord;

class DataCollectionRecordObserver
{
    public function created(DataCollectionRecord $dataCollectionRecord): void
    {
        DataCollectionRecordCreatedEvent::dispatch($dataCollectionRecord);
    }

    public function updated(DataCollectionRecord $dataCollectionRecord): void
    {
        DataCollectionRecordUpdatedEvent::dispatch($dataCollectionRecord);
    }

    public function deleted(DataCollectionRecord $dataCollectionRecord): void
    {
        DataCollectionRecordDeletedEvent::dispatch($dataCollectionRecord);
    }
}
