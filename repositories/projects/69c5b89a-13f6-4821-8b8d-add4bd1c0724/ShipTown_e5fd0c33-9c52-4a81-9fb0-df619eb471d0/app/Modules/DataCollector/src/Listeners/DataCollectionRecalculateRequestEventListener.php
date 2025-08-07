<?php

namespace App\Modules\DataCollector\src\Listeners;

use App\Events\DataCollection\DataCollectionRecalculateRequestEvent;
use App\Models\DataCollectionTransaction;
use App\Modules\DataCollector\src\Jobs\CalculateUnitTaxJob;
use Illuminate\Support\Facades\Cache;

class DataCollectionRecalculateRequestEventListener
{
    public function handle(DataCollectionRecalculateRequestEvent $event): void
    {
        if ($event->dataCollection->type !== DataCollectionTransaction::class) {
            return;
        }

        $lockKey = 'recalculating_unit_tax_lock_'.$event->dataCollection->id;

        Cache::lock($lockKey, 5)->get(function () use ($event) {
            CalculateUnitTaxJob::dispatch($event->dataCollection->getKey());
        });
    }
}
