<?php

namespace App\Modules\InventoryMovementsDailyStatistics\src\Listeners;

use App\Modules\InventoryMovementsDailyStatistics\src\Jobs\InsertDailyStatisticsRecordsJob;
use App\Modules\InventoryMovementsDailyStatistics\src\Jobs\RecalculateRecordsJob;

class EveryMinuteEventListener
{
    public function handle(): void
    {
        InsertDailyStatisticsRecordsJob::dispatch();
        RecalculateRecordsJob::dispatch();
    }
}
