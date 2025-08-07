<?php

namespace App\Modules\Magento2API\PriceSync\src\Listeners;

use App\Modules\Magento2API\PriceSync\src\Jobs\RecheckExistsInMagentoJob;

class EveryDayEventListener
{
    public function handle(): void
    {
        RecheckExistsInMagentoJob::dispatch();
    }
}
