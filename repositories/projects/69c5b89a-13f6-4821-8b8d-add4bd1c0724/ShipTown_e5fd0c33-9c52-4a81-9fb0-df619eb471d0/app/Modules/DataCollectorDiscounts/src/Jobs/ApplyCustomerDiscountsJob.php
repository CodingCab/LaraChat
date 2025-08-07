<?php

namespace App\Modules\DataCollectorDiscounts\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Models\DataCollection;
use App\Modules\DataCollectorDiscounts\src\Services\DiscountsService;
use Illuminate\Support\Facades\Cache;

class ApplyCustomerDiscountsJob extends UniqueJob
{
    private DataCollection $dataCollection;

    public function uniqueId(): string
    {
        return implode('_', [self::class, $this->dataCollection->id]);
    }

    public function __construct(DataCollection $dataCollection)
    {
        $this->dataCollection = $dataCollection;
    }

    public function handle(): void
    {
        $cacheLockKey = implode('-', [
            'recalculating_customer_discounts_for_data_collection',
            $this->dataCollection->id,
        ]);

        if ($this->dataCollection->billingAddress->discount) {
            Cache::lock($cacheLockKey, 5)->get(function () {
                DiscountsService::applyDiscounts($this->dataCollection, $this->dataCollection->billingAddress->discount);
            });
        } else {
            Cache::lock($cacheLockKey, 5)->get(function () {
                DiscountsService::applyDefaultPrices($this->dataCollection);
            });
        }
    }
}
