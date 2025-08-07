<?php

namespace App\Modules\DataCollectorSalePrices\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Models\DataCollection;
use App\Models\DataCollectionRecord;
use Illuminate\Support\Facades\Cache;

class ApplySalePricesJob extends UniqueJob
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
        $cacheLockKey = implode('-', ['applying_sale_prices_on_data_collection', $this->dataCollection->id]);

        Cache::lock($cacheLockKey, 5)->get(function () {
            $this->addSalePrice();
        });
    }

    public function addSalePrice(): void
    {
        $records = $this->dataCollection->records()
            ->with('prices')
            ->where(function ($query) {
                $query->whereNull('price_source')
                    ->orWhere('price_source', 'SALE_PRICE');
            })
            ->get();

        $records->each(function (DataCollectionRecord $record) {
            if (empty($record->prices)) {
                return true;
            }

            $isSalePriceActive = now()->isBetween($record->prices->sale_price_start_date, $record->prices->sale_price_end_date);

            if ($isSalePriceActive && $record->price_source === null) {
                $record->update([
                    'unit_sold_price' => $record->prices['sale_price'],
                    'price_source' => 'SALE_PRICE',
                    'price_source_id' => null,
                    'recalculate_unit_tax' => 1,
                ]);

                return true;
            }

            if ($isSalePriceActive === false && $record->price_source !== null) {
                $record->update([
                    'unit_sold_price' => $record->prices['price'],
                    'price_source' => null,
                    'price_source_id' => null,
                    'recalculate_unit_tax' => 1,
                ]);

                return true;
            }

            return true;
        });
    }
}
