<?php

namespace App\Modules\DataCollectorOfflineInventoryReserveStock\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Models\DataCollection;
use App\Models\DataCollectionOfflineInventory;
use App\Models\DataCollectionRecord;
use App\Modules\InventoryQuantityReserved\src\Services\QuantityReservedService;
use Illuminate\Support\Facades\DB;

class CheckReservationsDataIntegrityJob extends UniqueJob
{
    public function handle(): void
    {
        $this->addMissingReservations();
    }

    private function addMissingReservations(): void
    {
        $dataCollections = DataCollection::where('type', DataCollectionOfflineInventory::class)->get();

        foreach ($dataCollections as $dataCollection) {
            $dataCollectionRecords = $dataCollection->records;

            DB::statement('DROP TEMPORARY TABLE IF EXISTS temp_inventory_reservations');

            DB::statement('CREATE TEMPORARY TABLE temp_inventory_reservations AS
                SELECT dcr.id, dcr.inventory_id, dcr.quantity_scanned, dcr.warehouse_code,
                CONCAT("data_collection_record_id_", dcr.id) as custom_uuid, p.sku as product_sku
                FROM data_collection_records dcr
                JOIN products p ON dcr.product_id = p.id
                WHERE dcr.data_collection_id = ?', [$dataCollection->id]);

            DB::statement('INSERT INTO inventory_reservations (custom_uuid, inventory_id, quantity_reserved, warehouse_code, product_sku)
                SELECT custom_uuid, inventory_id, quantity_scanned, warehouse_code, product_sku FROM temp_inventory_reservations
                ON DUPLICATE KEY UPDATE quantity_reserved = VALUES(quantity_reserved)');

            DB::statement('DROP TEMPORARY TABLE IF EXISTS temp_inventory_reservations');

            DB::transaction(function () use ($dataCollectionRecords) {
                DataCollectionRecord::whereIn('id', $dataCollectionRecords->pluck('id'))->update(['is_reserved' => true]);
                QuantityReservedService::recalculateQuantityReserved(collect($dataCollectionRecords->pluck('inventory_id')));
            });
        }
    }
}
