<?php

namespace App\Modules\Reports\src\Listeners;

use App\Modules\Reports\src\Jobs\DispatchSaveInventoryDashboardReport;

class EveryHourEventListener
{
    public function handle(): void
    {
        DispatchSaveInventoryDashboardReport::dispatch();
    }
}
