<?php

namespace App\Modules\Magento2API\InventorySync\src\Listeners;

use App\Modules\Magento2API\InventorySync\src\Jobs\RecheckIfProductsExistInMagentoJob;

class EveryDayEventListener
{
    public function handle(): void
    {
        RecheckIfProductsExistInMagentoJob::dispatch();
    }
}
