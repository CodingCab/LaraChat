<?php

namespace App\Modules\DataCollectorQuantityDiscounts\src\Listeners;

use App\Events\DataCollectionRecord\DataCollectionRecordCreatedEvent;
use App\Models\DataCollectionTransaction;
use App\Modules\DataCollectorQuantityDiscounts\src\Services\QuantityDiscountsService;

class DataCollectionRecordCreatedEventListener
{
    public function handle(DataCollectionRecordCreatedEvent $event): void
    {
        if ($event->dataCollectionRecord->dataCollection->type !== DataCollectionTransaction::class) {
            return;
        }

        QuantityDiscountsService::dispatchQuantityDiscountJobs($event->dataCollectionRecord);
    }
}
