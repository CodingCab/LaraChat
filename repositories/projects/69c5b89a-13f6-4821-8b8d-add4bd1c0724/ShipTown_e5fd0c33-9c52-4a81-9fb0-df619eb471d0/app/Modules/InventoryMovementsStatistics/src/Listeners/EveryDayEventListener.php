<?php

namespace App\Modules\InventoryMovementsStatistics\src\Listeners;

use App\Modules\InventoryMovementsStatistics\src\Jobs\RecalculateStatisticsTableJob;

class EveryDayEventListener
{
    public function handle()
    {
        RecalculateStatisticsTableJob::dispatch();
    }
}
