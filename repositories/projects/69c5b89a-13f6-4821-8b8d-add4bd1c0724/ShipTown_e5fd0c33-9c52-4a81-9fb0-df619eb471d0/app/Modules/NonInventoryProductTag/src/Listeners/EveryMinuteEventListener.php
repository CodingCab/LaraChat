<?php

namespace App\Modules\NonInventoryProductTag\src\Listeners;

use App\Modules\NonInventoryProductTag\src\Jobs\ClearArcadiaStockJob;

class EveryMinuteEventListener
{
    public function handle(): void
    {
        ClearArcadiaStockJob::dispatch();
    }
}
