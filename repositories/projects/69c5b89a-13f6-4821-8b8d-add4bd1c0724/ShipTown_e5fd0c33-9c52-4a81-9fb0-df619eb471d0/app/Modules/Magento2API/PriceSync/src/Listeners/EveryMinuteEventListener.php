<?php

namespace App\Modules\Magento2API\PriceSync\src\Listeners;

use App\Modules\Magento2API\PriceSync\src\Jobs\CheckIfSyncIsRequiredJob;
use App\Modules\Magento2API\PriceSync\src\Jobs\CheckIfProductsExistJob;

class EveryMinuteEventListener
{
    public function handle(): void
    {
        CheckIfSyncIsRequiredJob::dispatch();
        CheckIfProductsExistJob::dispatch();
    }
}
