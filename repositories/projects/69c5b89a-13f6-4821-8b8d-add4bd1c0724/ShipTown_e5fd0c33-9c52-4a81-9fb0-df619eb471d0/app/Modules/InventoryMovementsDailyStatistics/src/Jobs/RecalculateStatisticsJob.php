<?php

namespace App\Modules\InventoryMovementsDailyStatistics\src\Jobs;

use App\Abstracts\UniqueJob;

class RecalculateStatisticsJob extends UniqueJob
{
    public function handle(): void
    {
        CreateDaysRecordsJob::dispatch();
        InsertDailyStatisticsRecordsJob::dispatch();
        RecalculateRecordsJob::dispatch();
    }
}
