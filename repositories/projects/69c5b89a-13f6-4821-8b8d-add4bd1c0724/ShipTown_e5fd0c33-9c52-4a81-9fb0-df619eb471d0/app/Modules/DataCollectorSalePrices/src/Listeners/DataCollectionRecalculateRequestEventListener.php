<?php

namespace App\Modules\DataCollectorSalePrices\src\Listeners;

use App\Events\DataCollection\DataCollectionRecalculateRequestEvent;
use App\Models\DataCollectionTransaction;
use App\Modules\DataCollectorSalePrices\src\Jobs\ApplySalePricesJob;
use Illuminate\Support\Facades\Cache;

class DataCollectionRecalculateRequestEventListener
{
    public function handle(DataCollectionRecalculateRequestEvent $event): void
    {
        if ($event->dataCollection->type !== DataCollectionTransaction::class) {
            return;
        }

        $lockKey = 'recalculating_sale_prices_lock_' . $event->dataCollection->id;

        Cache::lock($lockKey, 5)->get(function () use ($event) {
            ApplySalePricesJob::dispatch($event->dataCollection);
        });
    }
}
