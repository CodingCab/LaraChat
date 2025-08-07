<?php

namespace App\Modules\DataCollector\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Models\DataCollection;
use App\Models\DataCollectionTransaction;
use App\Modules\DataCollectorDiscounts\src\Jobs\ApplyCustomerDiscountsJob;
use App\Modules\DataCollectorGroupRecords\src\Jobs\GroupRecordsJob;
use App\Modules\DataCollectorQuantityDiscounts\src\Services\QuantityDiscountsService;
use App\Modules\DataCollectorSalePrices\src\Jobs\ApplySalePricesJob;

class DispatchDataCollectorRecalculateRequestJob extends UniqueJob
{
    protected DataCollection $dataCollection;

    public function __construct(DataCollection $dataCollection)
    {
        $this->dataCollection = $dataCollection;
    }

    public function handle(): void
    {
        ray($this->dataCollection);
        if ($this->dataCollection->type === DataCollectionTransaction::class) {
            if ($this->dataCollection->billingAddress) {
                ApplyCustomerDiscountsJob::dispatch($this->dataCollection);
            }
            QuantityDiscountsService::dispatchQuantityDiscountJobs($this->dataCollection);
            ApplySalePricesJob::dispatch($this->dataCollection);
            GroupRecordsJob::dispatch($this->dataCollection);
            CalculateUnitTaxJob::dispatch($this->dataCollection->getKey());
        }
        RecountTotalsJob::dispatch($this->dataCollection->getKey());
    }
}
