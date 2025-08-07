<?php

namespace App\Modules\DataCollectorDiscounts\src\Listeners;

use App\Events\DataCollectionRecord\DataCollectionRecordCreatedEvent;
use App\Models\DataCollectionTransaction;
use App\Modules\DataCollectorDiscounts\src\Jobs\ApplyCustomerDiscountsJob;

class DataCollectionRecordCreatedEventListener
{
    public function handle(DataCollectionRecordCreatedEvent $event): void
    {
        if ($event->dataCollectionRecord->dataCollection->type !== DataCollectionTransaction::class || ! $event->dataCollectionRecord->dataCollection->billingAddress) {
            return;
        }

        ApplyCustomerDiscountsJob::dispatch($event->dataCollectionRecord->dataCollection);
    }
}
