<?php

namespace App\Modules\DataCollectorDiscounts\src\Listeners;

use App\Events\DataCollection\DataCollectionUpdatedEvent;
use App\Models\DataCollectionTransaction;
use App\Modules\DataCollectorDiscounts\src\Jobs\ApplyCustomerDiscountsJob;

class DataCollectionUpdatedEventListener
{
    public function handle(DataCollectionUpdatedEvent $event): void
    {
        if ($event->dataCollection->type !== DataCollectionTransaction::class || ! $event->dataCollection->billingAddress) {
            return;
        }

        ApplyCustomerDiscountsJob::dispatch($event->dataCollection);
    }
}
