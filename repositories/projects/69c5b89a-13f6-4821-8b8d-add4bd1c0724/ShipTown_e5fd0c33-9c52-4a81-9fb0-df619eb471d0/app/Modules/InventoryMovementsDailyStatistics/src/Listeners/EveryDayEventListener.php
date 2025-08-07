<?php

namespace App\Modules\InventoryMovementsDailyStatistics\src\Listeners;

use App\Modules\InventoryMovementsDailyStatistics\src\Jobs\CreateDaysRecordsJob;

class EveryDayEventListener
{
    public function handle(): void
    {
        CreateDaysRecordsJob::dispatch();
    }
}
