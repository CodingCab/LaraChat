<?php

namespace Tests\Modules\DataCollectorTransfersOutReserveStock\src\Jobs;

use App\Abstracts\JobTestAbstract;
use App\Models\Inventory;

class CheckReservationsDataIntegrityJobTest extends JobTestAbstract
{
    public function testCheckReservationsDataIntegrityJobTestDeletesReservationsWhenArchivingDataCollection(): void
    {
        $warehouse = \App\Models\Warehouse::factory()->create();
        $product = \App\Models\Product::factory()->create();

        $inventory = Inventory::query()->where([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
        ])->first();

        // Create a Transfer Out data collection with a record
        $dataCollection = \App\Models\DataCollection::factory()->create([
            'type' => \App\Models\DataCollectionTransferOut::class,
            'warehouse_id' => $inventory->warehouse_id,
            'warehouse_code' => $inventory->warehouse_code,
        ]);

        $record = \App\Models\DataCollectionRecord::factory()->create([
            'data_collection_id' => $dataCollection->id,
            'inventory_id' => $inventory->id,
            'warehouse_id' => $inventory->warehouse_id,
            'warehouse_code' => $inventory->warehouse_code,
            'quantity_requested' => 5,
            'quantity_scanned' => 5,
        ]);

        $customUuid = implode(':', ['data_collection_id', $dataCollection->id, 'data_collection_record_id', $record->id]);
        
        // Clean up any existing reservations first
        \App\Models\InventoryReservation::where('custom_uuid', $customUuid)->delete();
        
        \App\Models\InventoryReservation::factory()->create([
            'custom_uuid'        => $customUuid,
            'inventory_id'       => $inventory->id,
            'warehouse_code'     => $inventory->warehouse_code,
            'product_sku'        => $record->product->sku,
            'quantity_reserved'  => $record->quantity_scanned,
        ]);

        // Ensure reservation exists
        $this->assertDatabaseHas('inventory_reservations', ['custom_uuid' => $customUuid]);

        // Archive (soft-delete) the data collection
        $dataCollection->delete();

        // Run the integrity check job for this collection
        $job = new \App\Modules\DataCollectorTransfersOutReserveStock\src\Jobs\CheckReservationsDataIntegrityJob($dataCollection->id);
        $job->handle();

        // Reservation should be removed when archived
        $this->assertDatabaseMissing('inventory_reservations', ['custom_uuid' => $customUuid]);
        $this->assertDatabaseHas('inventory', ['quantity_reserved' => 0, 'id' => $inventory->id]);
    }
}
