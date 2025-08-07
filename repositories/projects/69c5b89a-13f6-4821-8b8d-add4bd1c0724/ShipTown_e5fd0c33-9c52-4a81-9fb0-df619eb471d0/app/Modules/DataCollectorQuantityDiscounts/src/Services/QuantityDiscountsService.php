<?php

namespace App\Modules\DataCollectorQuantityDiscounts\src\Services;

use App\Models\DataCollection;
use App\Models\DataCollectionRecord;
use App\Modules\DataCollectorQuantityDiscounts\src\Models\QuantityDiscount;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class QuantityDiscountsService
{
    public static function getRecordsEligibleForDiscount(
        DataCollection $dataCollection,
        QuantityDiscount $discount
    ): Builder {
        return $dataCollection->records()
            ->getQuery()
            ->whereIn('product_id', Arr::pluck($discount->products, 'product_id'))
            ->where(function ($query) {
                $query->whereNull('price_source')
                    ->orWhere(['price_source' => 'QUANTITY_DISCOUNT'])
                    ->orWhere(['price_source' => 'SALE_PRICE']);
            })
            ->where(function ($query) use ($discount) {
                $query->whereNull('price_source_id')
                    ->orWhere(['price_source_id' => $discount->id]);
            })
            ->orderBy('unit_full_price', 'ASC')
            ->orderBy('price_source', 'DESC')
            ->orderBy('quantity_scanned', 'DESC')
            ->orderBy('id', 'ASC');
    }

    public static function preselectEligibleRecords(
        DataCollection $dataCollection,
        QuantityDiscount $discount,
        $quantityToDistribute = null
    ): void {
        $eligibleRecords = self::getRecordsEligibleForDiscount($dataCollection, $discount)->get();

        $remainingQuantityToDistribute = $quantityToDistribute ?? $discount->total_quantity_per_discount * self::timesWeCanApplyOfferFor(
            $eligibleRecords,
            $discount
        );

        $eligibleRecords->each(function (DataCollectionRecord $record) use (
            &$remainingQuantityToDistribute,
            $discount
        ) {
            if ($remainingQuantityToDistribute <= 0) {
                // Ensure record does not have discount applied
                if ($record->price_source_id === $discount->id) {
                    $record->update([
                        'unit_sold_price' => $record->unit_full_price,
                        'price_source' => null,
                        'price_source_id' => null,
                    ]);
                }

                return true;
            }

            if ($record->quantity_scanned <= $remainingQuantityToDistribute) {
                $record->update([
                    'price_source' => 'QUANTITY_DISCOUNT',
                    'price_source_id' => $discount->id,
                ]);

                $remainingQuantityToDistribute -= $record->quantity_scanned;

                return true;
            }

            if ($record->quantity_scanned > $remainingQuantityToDistribute) {
                $record->update([
                    'quantity_scanned' => $record->quantity_scanned - $remainingQuantityToDistribute,
                    'unit_sold_price' => $record->unit_full_price,
                    'price_source' => null,
                    'price_source_id' => null,
                ]);

                $record->replicate()
                    ->fill([
                        'quantity_scanned' => $remainingQuantityToDistribute,
                        'price_source' => 'QUANTITY_DISCOUNT',
                        'price_source_id' => $discount->id,
                    ])
                    ->save();

                $remainingQuantityToDistribute = 0;

                return true;
            }

            return true;
        });
    }

    public static function applyDiscounts(Collection $eligibleRecords, int $quantityToDistribute, $price): void
    {
        $eligibleRecords->each(function (DataCollectionRecord $record) use (&$quantityToDistribute, $price) {
            $discountedPrice = is_callable($price) ? $price($record) : $price;

            if ($quantityToDistribute <= 0) {
                // Ensure record does not have discount applied
                if ($record->unit_sold_price != $record->unit_full_price) {
                    $record->update(['unit_sold_price' => $record->unit_full_price, 'recalculate_unit_tax' => 1]);
                }

                // nothing more to discount, continue to next record
                return true;
            }

            if ($discountedPrice > $record->unit_full_price) {
                $record->update(['unit_sold_price' => $record->unit_full_price, 'recalculate_unit_tax' => 1]);

                return true;
            }

            if ($record->quantity_scanned <= $quantityToDistribute) {
                $record->update(['unit_sold_price' => $discountedPrice, 'recalculate_unit_tax' => 1]);
                $quantityToDistribute -= $record->quantity_scanned;

                return true;
            }

            if ($record->quantity_scanned > $quantityToDistribute) {
                $record->update([
                    'quantity_scanned' => $record->quantity_scanned - $quantityToDistribute,
                    'unit_sold_price' => $record->unit_full_price,
                    'recalculate_unit_tax' => 1,
                ]);

                $record->replicate()
                    ->fill([
                        'quantity_scanned' => $quantityToDistribute,
                        'unit_sold_price' => $discountedPrice,
                        'recalculate_unit_tax' => 1,
                    ])
                    ->save();

                $quantityToDistribute = 0;

                return true;
            }

            return true;
        });
    }

    public static function timesWeCanApplyOfferFor(Collection $records, QuantityDiscount $discount): int
    {
        return floor($records->sum('quantity_scanned') / $discount->total_quantity_per_discount);
    }

    public static function dispatchQuantityDiscountJobs(DataCollection $dataCollection): void
    {
        QuantityDiscount::query()
            ->whereHas('products', function ($query) use ($dataCollection) {
                $query->whereIn('product_id', function ($subQuery) use ($dataCollection) {
                    $subQuery->select('product_id')
                        ->from('data_collection_records')
                        ->where('data_collection_id', $dataCollection->id);
                });
            })
            ->with('products')
            ->get()
            ->each(function (QuantityDiscount $quantityDiscount) use ($dataCollection) {
                dispatch(new $quantityDiscount->job_class($dataCollection, $quantityDiscount));
            });
    }
}
