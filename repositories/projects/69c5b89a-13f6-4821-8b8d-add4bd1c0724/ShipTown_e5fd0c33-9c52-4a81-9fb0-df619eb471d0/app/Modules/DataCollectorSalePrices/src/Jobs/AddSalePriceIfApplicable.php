<?php

namespace App\Modules\DataCollectorSalePrices\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Models\DataCollection;
use App\Models\DataCollectionRecord;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class AddSalePriceIfApplicable extends UniqueJob
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
            ->whereNull('price_source_id')
            ->whereNull('price_source')
            ->get();

        $records->each(function (DataCollectionRecord $record) {
            if (empty($record->prices)) {
                return true;
            }

            if (! now()->between(Carbon::parse($record->prices->sale_price_start_date), Carbon::parse($record->prices->sale_price_end_date))) {
                return true;
            }

            if ($record->price_source === null) {
                $record->update([
                    'unit_sold_price' => $record->prices['sale_price'],
                    'price_source' => 'SALE_PRICE',
                ]);
            }

            return true;
        });
    }
}
