<?php

namespace App\Modules\Magento2API\PriceSync\src\Listeners;

use App\Modules\Magento2API\PriceSync\src\Jobs\EnsureProductPriceIdIsFilledJob;
use App\Modules\Magento2API\PriceSync\src\Jobs\EnsureProductRecordsExistJob;
use App\Modules\Magento2API\PriceSync\src\Jobs\EnsureProductSkuIsFilledJob;
use App\Modules\Magento2API\PriceSync\src\Jobs\FetchBasePricesJob;
use App\Modules\Magento2API\PriceSync\src\Jobs\FetchSpecialPricesJob;
use App\Modules\Magento2API\PriceSync\src\Jobs\SyncProductBasePricesBulkJob;
use App\Modules\Magento2API\PriceSync\src\Jobs\SyncProductSalePricesBulkJob;

class EveryTenMinutesEventListener
{
    public function handle(): void
    {
        EnsureProductRecordsExistJob::dispatch();
        EnsureProductPriceIdIsFilledJob::dispatch();
        EnsureProductSkuIsFilledJob::dispatch();

        FetchBasePricesJob::dispatch();
        FetchSpecialPricesJob::dispatch();

        SyncProductBasePricesBulkJob::dispatch();
        SyncProductSalePricesBulkJob::dispatch();
    }
}
