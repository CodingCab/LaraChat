<?php

namespace App\Modules\InventoryQuantityIncoming\src\Listeners;

use App\Modules\InventoryQuantityIncoming\src\Jobs\FixIncorrectQuantityIncomingJob;

class DataCollectionDeletedEventListener
{
    public function handle(): void
    {
        FixIncorrectQuantityIncomingJob::dispatchAfterResponse();
    }
}
