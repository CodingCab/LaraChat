<?php

namespace App\Modules\DataCollectorTransfersOutReserveStock\src\Listeners;

use App\Events\DataCollectionRecord\DataCollectionRecordUpdatedEvent;
use App\Models\DataCollectionRecord;
use App\Models\DataCollectionTransferOut;
use App\Models\InventoryReservation;

class DataCollectionRecordUpdatedEventListener
{
    public function handle(DataCollectionRecordUpdatedEvent $event): void
    {
        $record = $event->dataCollectionRecord;

        if ($record->dataCollection->type !== DataCollectionTransferOut::class) {
            return;
        }

        $this->update($record);
    }

    public function update(DataCollectionRecord $record): void
    {
        $custom_uuid = implode(':', [
            'data_collection_id', $record->data_collection_id,
            'data_collection_record_id', $record->id
        ]);

        $inventoryReservation = InventoryReservation::query()
            ->where(['custom_uuid' => $custom_uuid])
            ->first();

        if ($inventoryReservation === null) {
            return;
        }

        $inventory = $record->inventory;

        $inventory->updateQuietly([
            'quantity_reserved' => $inventory->quantity_reserved - $inventoryReservation->quantity_reserved + $record->quantity_scanned,
            'recount_required' => true
        ]);

        $inventoryReservation->update([
            'quantity_reserved' => $record->quantity_reserved ?? 0,
        ]);
    }
}
