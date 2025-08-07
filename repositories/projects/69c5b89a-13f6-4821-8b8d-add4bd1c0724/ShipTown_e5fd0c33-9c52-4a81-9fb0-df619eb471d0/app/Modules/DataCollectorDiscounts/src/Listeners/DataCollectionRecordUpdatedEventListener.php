<?php

namespace App\Modules\DataCollectorDiscounts\src\Listeners;

use App\Events\DataCollectionRecord\DataCollectionRecordUpdatedEvent;
use App\Models\DataCollectionTransaction;
use App\Modules\DataCollectorDiscounts\src\Jobs\ApplyCustomerDiscountsJob;

class DataCollectionRecordUpdatedEventListener
{
    public function handle(DataCollectionRecordUpdatedEvent $event): void
    {
        if ($event->dataCollectionRecord->dataCollection->type !== DataCollectionTransaction::class || ! $event->dataCollectionRecord->dataCollection->billingAddress) {
            return;
        }

        ApplyCustomerDiscountsJob::dispatch($event->dataCollectionRecord->dataCollection);
    }
}
