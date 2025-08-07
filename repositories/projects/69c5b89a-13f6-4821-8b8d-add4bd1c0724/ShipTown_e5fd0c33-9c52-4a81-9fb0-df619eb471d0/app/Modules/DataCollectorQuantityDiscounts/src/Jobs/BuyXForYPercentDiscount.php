<?php

namespace App\Modules\DataCollectorQuantityDiscounts\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Models\DataCollection;
use App\Modules\DataCollectorQuantityDiscounts\src\Models\QuantityDiscount;
use App\Modules\DataCollectorQuantityDiscounts\src\Services\QuantityDiscountsService;
use Illuminate\Support\Facades\Cache;

class BuyXForYPercentDiscount extends UniqueJob
{
    private QuantityDiscount $discount;

    private DataCollection $dataCollection;

    public function uniqueId(): string
    {
        return implode('_', [self::class, $this->dataCollection->id]);
    }

    public function __construct(DataCollection $dataCollection, QuantityDiscount $discount)
    {
        $this->discount = $discount;
        $this->dataCollection = $dataCollection;
    }

    public function handle(): void
    {
        $cacheLockKey = implode('-', [
            'recalculating_quantity_discounts_for_data_collection',
            $this->dataCollection->id,
        ]);

        Cache::lock($cacheLockKey, 5)->get(function () {
            QuantityDiscountsService::preselectEligibleRecords($this->dataCollection, $this->discount);
            $this->applyDiscountsToSelectedRecords();
        });
    }

    public function applyDiscountsToSelectedRecords(): void
    {
        $eligibleRecords = QuantityDiscountsService::getRecordsEligibleForDiscount($this->dataCollection, $this->discount)
            ->where(['price_source_id' => $this->discount->id])
            ->get();

        $quantityToDistribute = $this->discount->quantity_required * QuantityDiscountsService::timesWeCanApplyOfferFor($eligibleRecords, $this->discount);

        QuantityDiscountsService::applyDiscounts(
            $eligibleRecords,
            $quantityToDistribute,
            function ($record) {
                return $record->unit_full_price - ($record->unit_full_price * ($this->discount->configuration['discount_percent'] / 100));
            }
        );
    }
}
