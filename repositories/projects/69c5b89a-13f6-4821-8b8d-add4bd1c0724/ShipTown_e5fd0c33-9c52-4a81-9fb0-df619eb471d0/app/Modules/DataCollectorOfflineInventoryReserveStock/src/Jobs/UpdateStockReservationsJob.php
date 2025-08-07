<?php

namespace App\Modules\DataCollectorOfflineInventoryReserveStock\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Models\DataCollectionRecord;
use App\Models\InventoryReservation;
use App\Modules\InventoryQuantityReserved\src\Services\QuantityReservedService;

class UpdateStockReservationsJob extends UniqueJob
{
    private DataCollectionRecord $dataCollectionRecord;
    private string $customUuid;

    public function uniqueId(): string
    {
        return implode('_', [self::class, $this->dataCollectionRecord->id]);
    }

    public function __construct(DataCollectionRecord $dataCollectionRecord)
    {
        $this->dataCollectionRecord = $dataCollectionRecord;
        $this->customUuid = 'data_collection_record_id_'.$dataCollectionRecord->id;
    }

    public function handle(): void
    {
        $inventoryReservation = InventoryReservation::firstOrCreate(
            ['custom_uuid' => $this->customUuid],
            [
                'warehouse_code' => $this->dataCollectionRecord->warehouse_code,
                'product_sku' => $this->dataCollectionRecord->product->sku,
                'inventory_id' => $this->dataCollectionRecord->inventory_id,
                'quantity_reserved' => 0,
                'comment' => 'Offline Inventory: ' . $this->dataCollectionRecord->dataCollection->name,
            ]
        );

        if ($this->dataCollectionRecord->is_reserved && $inventoryReservation->quantity_reserved === $this->dataCollectionRecord->quantity_scanned) {
            return;
        }

        $inventoryReservation->quantity_reserved = $this->dataCollectionRecord->quantity_scanned;
        $inventoryReservation->save();

        $this->dataCollectionRecord->is_reserved = true;
        $this->dataCollectionRecord->save();

        QuantityReservedService::recalculateQuantityReserved(collect([$this->dataCollectionRecord->inventory_id]));
    }
}
