<?php

namespace App\Modules\DataCollector\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Models\DataCollection;
use App\Models\DataCollectionRecord;

/**
 * Class SyncCheckFailedProductsJob.
 */
class CalculateUnitTaxJob extends UniqueJob
{
    public ?int $dataCollectionId;

    public function __construct(int $dataCollectionId)
    {
        $this->dataCollectionId = $dataCollectionId;
    }

    public function uniqueId(): string
    {
        return implode('-', [get_class($this), $this->dataCollectionId]);
    }

    public function handle(): void
    {
        $taxExempted = false;

        /** @var DataCollection $dataCollection */
        $dataCollection = DataCollection::find($this->dataCollectionId);
        if ($dataCollection->billingAddress && $dataCollection->billingAddress->tax_exempt) {
            $taxExempted = true;
        }

        /** @var DataCollectionRecord $records */
        $records = DataCollectionRecord::where('data_collection_id', $this->dataCollectionId)
            ->whereNotNull('sales_tax_code')
            ->where('recalculate_unit_tax', 1)
            ->get();

        $records->each(function (DataCollectionRecord $record) use ($taxExempted) {
            $record->update([
                'unit_tax' => $taxExempted ? 0 : $record->unit_sold_price * ($record->saleTax->rate / 100),
                'unit_sold_price' => $record->unit_sold_price - ($taxExempted ? $record->unit_sold_price * ($record->saleTax->rate / 100) : 0),
                'calculated_unit_tax' => $record->unit_sold_price * ($record->saleTax->rate / 100),
                'recalculate_unit_tax' => 0
            ]);
        });
    }
}
