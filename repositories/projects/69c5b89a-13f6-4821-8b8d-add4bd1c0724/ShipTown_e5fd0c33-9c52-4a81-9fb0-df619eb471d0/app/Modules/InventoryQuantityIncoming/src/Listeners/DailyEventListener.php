<?php

namespace App\Modules\InventoryQuantityIncoming\src\Listeners;

use App\Modules\InventoryQuantityIncoming\src\Jobs\FixIncorrectQuantityIncomingJob;

class DailyEventListener
{
    public function handle(): void
    {
        FixIncorrectQuantityIncomingJob::dispatch();
    }
}
