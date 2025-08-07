<?php

namespace App\Modules\InventoryTotals\src\Listeners;

use App\Modules\InventoryTotals\src\Jobs\RecalculateInventoryTotalsByWarehouseTagJob;
use App\Modules\InventoryTotals\src\Jobs\RecalculateInventoryTotalsTableJob;
use App\Modules\InventoryTotals\src\Jobs\RecalculateProductTotalsTableJob;

class EveryMinuteEventListener
{
    public function handle(): void
    {
        RecalculateInventoryTotalsTableJob::dispatch();
        RecalculateInventoryTotalsByWarehouseTagJob::dispatch();
        RecalculateProductTotalsTableJob::dispatch();
    }
}
