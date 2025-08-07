<?php

namespace App\Modules\DataCollectorOfflineInventoryReserveStock\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Models\DataCollection;
use App\Models\DataCollectionRecord;
use App\Models\InventoryReservation;
use App\Modules\InventoryQuantityReserved\src\Services\QuantityReservedService;
use Illuminate\Support\Facades\DB;

class DeleteStockReservationsJob extends UniqueJob
{
    private DataCollection $dataCollection;

    public function __construct(DataCollection $dataCollection)
    {
        $this->dataCollection = $dataCollection;
    }

    public function uniqueId(): string
    {
        return implode('_', [self::class, $this->dataCollection->id]);
    }

    public function handle(): void
    {
        do {
            $dataCollectionRecords = $this->dataCollection->records()
                ->whereNotNull('is_reserved')
                ->limit(50)
                ->get();

            if ($dataCollectionRecords->isEmpty()) {
                return;
            }

            DB::transaction(function () use ($dataCollectionRecords) {
                $customUuids = $dataCollectionRecords->map(function (DataCollectionRecord $record) {
                    return 'data_collection_record_id_'.$record->id;
                });

                InventoryReservation::query()->whereIn('custom_uuid', $customUuids)->delete();

                DataCollectionRecord::query()->whereIn('id', $dataCollectionRecords->pluck('id'))->update(['is_reserved' => null]);

                QuantityReservedService::recalculateQuantityReserved(collect($dataCollectionRecords->pluck('inventory_id')));
            });
        } while ($dataCollectionRecords->isNotEmpty());
    }
}
