<?php

namespace App\Modules\Magento2API\InventorySync\src\Listeners;

use App\Modules\Magento2API\InventorySync\src\Jobs\AssignInventorySourceJob;
use App\Modules\Magento2API\InventorySync\src\Jobs\CheckIfSyncIsRequiredJob;
use App\Modules\Magento2API\InventorySync\src\Jobs\FetchStockItemsJob;
use App\Modules\Magento2API\InventorySync\src\Jobs\GetProductIdsJob;
use App\Modules\Magento2API\InventorySync\src\Jobs\SyncProductInventoryJob;

class EveryFiveMinutesEventListener
{
    public function handle(): void
    {
        GetProductIdsJob::dispatch();
        AssignInventorySourceJob::dispatch();
        FetchStockItemsJob::dispatch();
        CheckIfSyncIsRequiredJob::dispatch();
        SyncProductInventoryJob::dispatch();
    }
}
