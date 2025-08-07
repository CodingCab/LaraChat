<?php

namespace App\Modules\Magento2API\PriceSync\src\Listeners;

use App\Modules\Magento2API\PriceSync\src\Jobs\SyncProductBasePricesJob;
use App\Modules\Magento2API\PriceSync\src\Jobs\SyncProductSalePricesJob;

class EveryHourEventListener
{
    public function handle(): void
    {
        SyncProductBasePricesJob::dispatch();
        SyncProductSalePricesJob::dispatch();
    }
}
