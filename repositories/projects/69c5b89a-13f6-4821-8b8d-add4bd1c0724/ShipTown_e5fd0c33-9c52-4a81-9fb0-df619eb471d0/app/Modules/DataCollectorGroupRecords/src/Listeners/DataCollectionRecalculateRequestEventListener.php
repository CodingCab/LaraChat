<?php

namespace App\Modules\DataCollectorGroupRecords\src\Listeners;

use App\Events\DataCollection\DataCollectionRecalculateRequestEvent;
use App\Models\DataCollectionTransaction;
use App\Modules\DataCollectorGroupRecords\src\Jobs\GroupRecordsJob;
use Illuminate\Support\Facades\Cache;

class DataCollectionRecalculateRequestEventListener
{
    public function handle(DataCollectionRecalculateRequestEvent $event): void
    {
        if ($event->dataCollection->type !== DataCollectionTransaction::class) {
            return;
        }

        $lockKey = 'recalculating_group_records_lock_'.$event->dataCollection->id;

        Cache::lock($lockKey, 5)->get(function () use ($event) {
            GroupRecordsJob::dispatch($event->dataCollection);
        });
    }
}
