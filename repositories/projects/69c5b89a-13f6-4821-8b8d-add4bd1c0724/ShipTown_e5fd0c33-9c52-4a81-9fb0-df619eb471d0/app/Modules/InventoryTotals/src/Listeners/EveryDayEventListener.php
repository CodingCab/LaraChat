<?php

namespace App\Modules\InventoryTotals\src\Listeners;

use App\Modules\InventoryTotals\src\Jobs\EnsureInventoryTotalsByWarehouseTagRecordsExistJob;

class EveryDayEventListener
{
    public function handle(): void
    {
        EnsureInventoryTotalsByWarehouseTagRecordsExistJob::dispatch();
    }
}
