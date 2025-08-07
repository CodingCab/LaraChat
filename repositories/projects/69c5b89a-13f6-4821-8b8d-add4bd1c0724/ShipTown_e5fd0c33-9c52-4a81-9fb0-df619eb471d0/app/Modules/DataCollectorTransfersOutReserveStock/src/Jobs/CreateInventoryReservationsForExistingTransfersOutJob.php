<?php

namespace App\Modules\DataCollectorTransfersOutReserveStock\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Models\DataCollection;
use App\Models\DataCollectionRecord;
use App\Models\DataCollectionTransferOut;
use App\Models\InventoryReservation;
use App\Modules\InventoryQuantityReserved\src\Services\QuantityReservedService;
use Illuminate\Support\Facades\DB;

class CreateInventoryReservationsForExistingTransfersOutJob extends UniqueJob
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
                    ->limit(50)
                    ->get();

                if ($dataCollectionRecords->isEmpty()) {
                    return;
                }

                $inventoryReservationRecords = $dataCollectionRecords->map(function (DataCollectionRecord $record) {
                    return [
                        'inventory_id' => $record->inventory_id,
                        'product_sku' => $record->product->sku,
                        'warehouse_code' => $record->warehouse_code,
                        'quantity_reserved' => $record->quantity_requested,
                        'comment' => 'Transfers Out: ' . $record->dataCollection->name,
                        'custom_uuid' => implode(':', [
                            'data_collection_id', $record->data_collection_id,
                            'data_collection_record_id', $record->id
                        ]),
                    ];
                });

                DB::transaction(function () use ($inventoryReservationRecords, $dataCollectionRecords) {
                    InventoryReservation::query()->upsert($inventoryReservationRecords->toArray(), ['custom_uuid']);

                    DataCollectionRecord::query()->whereIn('id', $dataCollectionRecords->pluck('id'))->update(['is_reserved' => true]);

                    QuantityReservedService::recalculateQuantityReserved(collect($inventoryReservationRecords->pluck('inventory_id')));
                });
            } while ($dataCollectionRecords->isNotEmpty());
        });
    }
}
