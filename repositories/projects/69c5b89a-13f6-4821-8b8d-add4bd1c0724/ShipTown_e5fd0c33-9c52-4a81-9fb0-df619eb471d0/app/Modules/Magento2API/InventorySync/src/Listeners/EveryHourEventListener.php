<?php

namespace App\Modules\Magento2API\InventorySync\src\Listeners;

use App\Modules\Magento2API\InventorySync\src\Jobs\EnsureInventoryGroupIdIsNotNullJob;
use App\Modules\Magento2API\InventorySync\src\Jobs\EnsureProductRecordsExistJob;

class EveryHourEventListener
{
    public function handle(): void
    {
        EnsureProductRecordsExistJob::dispatch();
        EnsureInventoryGroupIdIsNotNullJob::dispatch();
    }
}
