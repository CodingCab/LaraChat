<?php

namespace App\Modules\DataCollectorTransfersOutReserveStock\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Models\DataCollection;
use App\Models\DataCollectionRecord;
use App\Models\DataCollectionTransferOut;
use App\Models\InventoryReservation;
use App\Modules\InventoryQuantityReserved\src\Services\QuantityReservedService;
use Illuminate\Support\Facades\DB;

class DeleteExistingInventoryReservationsJob extends UniqueJob
{
    public function handle(): void
    {
        $dataCollections = DataCollection::query()
            ->where('type', DataCollectionTransferOut::class)
            ->withoutTrashed()
            ->get();

        $dataCollections->each(function (DataCollection $dataCollection) {
            do {
                $dataCollectionRecords = $dataCollection->records()
                    ->whereNotNull('is_reserved')
                    ->limit(50)
                    ->get();

                if ($dataCollectionRecords->isEmpty()) {
                    return;
                }

                DB::transaction(function () use ($dataCollectionRecords) {
                    $customUuids = $dataCollectionRecords->map(function (DataCollectionRecord $record) {
                        return implode(':', [
                            'data_collection_id', $record->data_collection_id,
                            'data_collection_record_id', $record->id
                        ]);
                    });

                    InventoryReservation::query()
                        ->whereIn('custom_uuid', $customUuids)
                        ->delete();

                    DataCollectionRecord::query()
                        ->whereIn('id', $dataCollectionRecords->pluck('id'))
                        ->update(['is_reserved' => null]);

                    $inventoryIds = collect($dataCollectionRecords->pluck('inventory_id'));

                    QuantityReservedService::recalculateQuantityReserved($inventoryIds);
                });
            } while ($dataCollectionRecords->isNotEmpty());
        });
    }
}
