<?php

namespace App\Modules\DataCollectorOfflineInventoryReserveStock\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Models\DataCollection;
use App\Models\DataCollectionOfflineInventory;
use App\Models\DataCollectionRecord;
use App\Models\InventoryReservation;
use App\Modules\InventoryQuantityReserved\src\Services\QuantityReservedService;
use Illuminate\Support\Facades\DB;

class CreateInventoryReservationsForExistingOfflineInventoryJob extends UniqueJob
{
    public function handle(): void
    {
        $dataCollections = DataCollection::where('type', DataCollectionOfflineInventory::class)->withoutTrashed()->get();

        $dataCollections->each(function (DataCollection $dataCollection) {
            do {
                $dataCollectionRecords = $dataCollection->records()
                    ->where('quantity_scanned', '>', 0)
                    ->whereNull('is_reserved')
                    ->limit(50)
                    ->get();

                if ($dataCollectionRecords->isEmpty()) {
                    return;
                }

                $inventoryReservationRecords = $dataCollectionRecords->map(function (DataCollectionRecord $record) use ($dataCollection) {
                    return [
                        'inventory_id' => $record->inventory_id,
                        'product_sku' => $record->product->sku,
                        'warehouse_code' => $record->warehouse_code,
                        'quantity_reserved' => $record->quantity_scanned,
                        'comment' => 'Offline Inventory: ' . $dataCollection->name,
                        'custom_uuid' => 'data_collection_record_id_'.$record->getKey(),
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
