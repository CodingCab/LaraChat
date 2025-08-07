<?php

namespace App\Modules\Api2cart\src\Listeners;

use App\Modules\Api2cart\src\Jobs\DispatchSyncOrderStatusJobsJob;
use App\Modules\Api2cart\src\Jobs\ProcessImportedOrdersJob;

class EveryMinuteEventListener
{
    public function handle(): void
    {
        ProcessImportedOrdersJob::dispatch();
        DispatchSyncOrderStatusJobsJob::dispatch();
    }
}
