<?php

namespace App\Modules\DataCollectorTransfersOutReserveStock\src\Listeners;

use App\Events\DataCollectionRecord\DataCollectionRecordDeletedEvent;
use App\Models\DataCollectionTransferOut;
use App\Models\InventoryReservation;

class DataCollectionRecordDeletedEventListener
{
    public function handle(DataCollectionRecordDeletedEvent $event): void
    {
        $record = $event->dataCollectionRecord->refresh();

        if ($record->dataCollection->type !== DataCollectionTransferOut::class) {
            return;
        }

        $this->delete($record);
    }

    public function delete(\App\Models\DataCollectionRecord $record): void
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

        $record->inventory->updateQuietly([
            'quantity_reserved' => $record->inventory->quantity_reserved - $inventoryReservation->quantity_reserved,
            'recount_required' => true
        ]);

        $inventoryReservation->delete();
    }
}
