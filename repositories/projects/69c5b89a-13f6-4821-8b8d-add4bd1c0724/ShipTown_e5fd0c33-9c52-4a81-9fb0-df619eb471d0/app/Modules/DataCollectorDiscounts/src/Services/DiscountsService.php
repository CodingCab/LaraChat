<?php

namespace App\Modules\DataCollectorDiscounts\src\Services;

use App\Models\DataCollection;
use App\Models\DataCollectionRecord;
use App\Modules\DataCollectorDiscounts\src\Models\Discount;
use Illuminate\Database\Eloquent\Builder;

class DiscountsService
{
    public static function getRecordsEligibleForDiscount(DataCollection $dataCollection): Builder
    {
        return $dataCollection->records()
            ->getQuery()
            ->where(function ($query) {
                $query->whereNull('price_source')
                    ->orWhere(['price_source' => 'CUSTOMER_DISCOUNT'])
                    ->orWhere(['price_source' => 'QUANTITY_DISCOUNT'])
                    ->orWhere(['price_source' => 'SALE_PRICE']);
            })
            ->orderBy('unit_full_price', 'ASC')
            ->orderBy('price_source', 'DESC')
            ->orderBy('quantity_scanned', 'DESC')
            ->orderBy('id', 'ASC');
    }

    public static function applyDiscounts(DataCollection $dataCollection, Discount $discount): void
    {
        $eligibleRecords = self::getRecordsEligibleForDiscount($dataCollection)->get();

        $eligibleRecords->each(function (DataCollectionRecord $record) use ($discount) {
            $discountedPrice = $record->unit_full_price * (1 - $discount->percentage_discount / 100);

            if ($discountedPrice > $record->unit_full_price) {
                $record->update(['unit_sold_price' => $record->unit_full_price]);

                return true;
            }

            if ($discountedPrice < $record->unit_sold_price) {
                $record->update([
                    'unit_sold_price' => $discountedPrice,
                    'price_source' => 'CUSTOMER_DISCOUNT',
                ]);
            }

            return true;
        });
    }

    public static function applyDefaultPrices(DataCollection $dataCollection): void
    {
        $eligibleRecords = self::getRecordsEligibleForDiscount($dataCollection)->get();

        $eligibleRecords->each(function (DataCollectionRecord $record) {
            $record->update([
                'unit_sold_price' => $record->prices->current_price,
            ]);
        });
    }
}
