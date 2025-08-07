<?php

namespace App\Modules\InventoryQuantityIncoming\src\Listeners;

use App\Events\DataCollectionRecord\DataCollectionRecordDeletedEvent;
use App\Models\DataCollectionTransferIn;
use App\Modules\InventoryQuantityIncoming\src\Jobs\RecalculateInventoryQuantityIncomingJob;

class DataCollectionRecordDeletedEventListener
{
    public function handle(DataCollectionRecordDeletedEvent $event)
    {
        $record = $event->dataCollectionRecord;

        if ($record->dataCollection->type === DataCollectionTransferIn::class) {
            RecalculateInventoryQuantityIncomingJob::dispatchSync(
                $record->product_id,
                $record->dataCollection->warehouse_id
            );
        }
    }
}
