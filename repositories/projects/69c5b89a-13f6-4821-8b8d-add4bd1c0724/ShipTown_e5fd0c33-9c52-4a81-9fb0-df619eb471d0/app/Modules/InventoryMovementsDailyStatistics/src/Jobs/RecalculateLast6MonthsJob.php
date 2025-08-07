<?php

namespace App\Modules\InventoryMovementsDailyStatistics\src\Jobs;

use App\Abstracts\UniqueJob;

class RecalculateLast6MonthsJob extends UniqueJob
{
    public function handle(): void
    {
        $date = now()->subMonths(6);

        do {
            $fromDateTime = $date->clone()->startOfDay();

//            Log::info(implode(['Dispatching Jobs for ', $fromDateTime->toDateString()]));

            RecalculateRecordsJob::dispatch();

            $date = $date->addDay();
        } while (now()->isAfter($date));
    }
}
