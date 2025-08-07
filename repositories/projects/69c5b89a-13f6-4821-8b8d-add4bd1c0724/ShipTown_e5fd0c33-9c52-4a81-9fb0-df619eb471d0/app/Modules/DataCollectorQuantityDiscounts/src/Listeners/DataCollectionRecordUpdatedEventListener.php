<?php

namespace App\Modules\DataCollectorQuantityDiscounts\src\Listeners;

use App\Events\DataCollectionRecord\DataCollectionRecordUpdatedEvent;
use App\Models\DataCollectionTransaction;
use App\Modules\DataCollectorQuantityDiscounts\src\Services\QuantityDiscountsService;

class DataCollectionRecordUpdatedEventListener
{
    public function handle(DataCollectionRecordUpdatedEvent $event): void
    {
        if ($event->dataCollectionRecord->dataCollection->type !== DataCollectionTransaction::class) {
            return;
        }

        QuantityDiscountsService::dispatchQuantityDiscountJobs($event->dataCollectionRecord);
    }
}
