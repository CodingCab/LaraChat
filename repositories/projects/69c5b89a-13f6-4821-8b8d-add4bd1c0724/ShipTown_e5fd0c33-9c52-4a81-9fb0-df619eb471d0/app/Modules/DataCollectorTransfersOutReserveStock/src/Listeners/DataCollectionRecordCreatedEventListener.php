<?php

namespace App\Modules\DataCollectorTransfersOutReserveStock\src\Listeners;

use App\Events\DataCollectionRecord\DataCollectionRecordCreatedEvent;
use App\Models\DataCollectionRecord;
use App\Models\DataCollectionTransferOut;
use App\Models\InventoryReservation;

class DataCollectionRecordCreatedEventListener
{
    public function handle(DataCollectionRecordCreatedEvent $event): void
    {
        $record = $event->dataCollectionRecord->refresh();

        if ($record->dataCollection->type !== DataCollectionTransferOut::class) {
            return;
        }

        if ($record->quantity_scanned == 0 || $record->quantity_scanned === null) {
            return;
        }

        $this->reserve($record);
    }

    public function reserve(DataCollectionRecord $record): void
    {
        InventoryReservation::query()
            ->create([
                'custom_uuid' => implode(':', [
                    'data_collection_id', $record->data_collection_id,
                    'data_collection_record_id', $record->id
                ]),
                'warehouse_code' => $record->warehouse_code,
                'product_sku' => $record->product->sku,
                'inventory_id' => $record->inventory_id,
                'quantity_reserved' => $record->quantity_scanned,
                'comment' => 'Transfers Out: ' . $record->dataCollection->name,
            ]);

        $record->inventory->updateQuietly([
            'quantity_reserved' => $record->inventory->quantity_reserved + $record->quantity_scanned,
            'recount_required' => true
        ]);
    }
}
