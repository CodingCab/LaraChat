<?php

namespace App\Modules\Maintenance\src\Listeners;

use App\Modules\Maintenance\src\Jobs\FillInventoryIdInProductsPricesTableJob;
use App\Modules\Maintenance\src\Jobs\FillTagNameInTaggableTableJob;
use App\Modules\Maintenance\src\Jobs\FillUnitSoldPriceInOrdersProductsTableJob;
use App\Modules\Maintenance\src\Jobs\FixNullUnitPriceInInventoryMovementsJob;

class EveryMinuteEventListener
{
    public function handle(): void
    {
        FillTagNameInTaggableTableJob::dispatch();
        FillInventoryIdInProductsPricesTableJob::dispatch();
        FillUnitSoldPriceInOrdersProductsTableJob::dispatch();
        FixNullUnitPriceInInventoryMovementsJob::dispatch();
    }
}
