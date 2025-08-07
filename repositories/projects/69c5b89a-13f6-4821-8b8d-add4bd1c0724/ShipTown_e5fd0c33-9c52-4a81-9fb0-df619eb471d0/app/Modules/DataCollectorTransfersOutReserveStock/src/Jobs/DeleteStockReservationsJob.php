<?php

namespace App\Modules\DataCollectorTransfersOutReserveStock\src\Jobs;

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
                ->limit(50)
                ->get();

            if ($dataCollectionRecords->isEmpty()) {
                return;
            }

            DB::transaction(function () use ($dataCollectionRecords) {
                $customUuids = $dataCollectionRecords->map(function (DataCollectionRecord $record) {
                    return implode(':', [
                        'data_collection_id', $record->data_collection_id,
                        'data_collection_record_id' . $record->id
                    ]);
                });

                InventoryReservation::query()
                    ->whereIn('custom_uuid', $customUuids)
                    ->delete();

                $recordIds = $dataCollectionRecords->pluck('id');

                DataCollectionRecord::query()
                    ->whereIn('id', $recordIds)
                    ->update(['is_reserved' => null]);

                $inventoryIds = collect($dataCollectionRecords->pluck('inventory_id'));
                QuantityReservedService::recalculateQuantityReserved($inventoryIds);
            });
        } while ($dataCollectionRecords->isNotEmpty());
    }
}
