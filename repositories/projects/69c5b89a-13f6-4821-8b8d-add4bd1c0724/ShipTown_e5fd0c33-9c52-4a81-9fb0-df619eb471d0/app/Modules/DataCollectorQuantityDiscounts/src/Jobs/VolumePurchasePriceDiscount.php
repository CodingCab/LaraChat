<?php

namespace App\Modules\DataCollectorQuantityDiscounts\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Models\DataCollection;
use App\Modules\DataCollectorQuantityDiscounts\src\Models\QuantityDiscount;
use App\Modules\DataCollectorQuantityDiscounts\src\Services\QuantityDiscountsService;
use Illuminate\Support\Facades\Cache;

class VolumePurchasePriceDiscount extends UniqueJob
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
            $quantityScanned = QuantityDiscountsService::getRecordsEligibleForDiscount($this->dataCollection, $this->discount)
                ->sum('quantity_scanned');

            $minQuantityRequired = collect($this->discount->configuration['multibuy_discount_ranges'])
                ->min('minimum_quantity');

            $quantityToDiscount = $quantityScanned < $minQuantityRequired ? 0 : $quantityScanned;

            QuantityDiscountsService::preselectEligibleRecords($this->dataCollection, $this->discount, $quantityToDiscount);
            $this->applyDiscountsToSelectedRecords();
        });
    }

    public function applyDiscountsToSelectedRecords(): void
    {
        $eligibleRecords = QuantityDiscountsService::getRecordsEligibleForDiscount($this->dataCollection, $this->discount)
            ->where(['price_source_id' => $this->discount->id])
            ->get();

        $config = collect($this->discount->configuration['multibuy_discount_ranges']);
        $quantityToDistribute = $eligibleRecords->sum('quantity_scanned') < $config->min('minimum_quantity')
            ? 0
            : $eligibleRecords->sum('quantity_scanned');
        $correctDiscount = $config
            ->where('minimum_quantity', '<=', $quantityToDistribute)
            ->sortByDesc('minimum_quantity')
            ->first();
        $discountedPrice = $correctDiscount['discounted_price'] ?? 0;

        QuantityDiscountsService::applyDiscounts(
            $eligibleRecords,
            $quantityToDistribute,
            $discountedPrice
        );
    }
}
