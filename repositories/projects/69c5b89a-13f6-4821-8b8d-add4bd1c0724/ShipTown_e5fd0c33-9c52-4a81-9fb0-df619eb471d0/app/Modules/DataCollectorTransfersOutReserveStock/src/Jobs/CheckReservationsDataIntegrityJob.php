<?php

namespace App\Modules\DataCollectorTransfersOutReserveStock\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Models\DataCollection;
use App\Models\DataCollectionRecord;
use App\Models\DataCollectionTransferOut;
use App\Models\Inventory;
use App\Models\InventoryReservation;
use App\Modules\InventoryQuantityReserved\src\Services\QuantityReservedService;
use Illuminate\Support\Facades\DB;

class CheckReservationsDataIntegrityJob extends UniqueJob
{
    private ?int $dataCollection_id;

    public function __construct(int $dataCollection_id = null)
    {
        $this->dataCollection_id = $dataCollection_id;
    }

    public function handle(): void
    {
        if ($this->dataCollection_id !== null) {
            InventoryReservation::query()
                ->whereLike('custom_uuid', 'data_collection_id:' . $this->dataCollection_id . ':%')
                ->delete();
        } else {
            InventoryReservation::query()
                ->whereLike('custom_uuid', 'data_collection_id:%')
                ->delete();
        }

        $dataCollections = DataCollection::query()
            ->whereNull('deleted_at')
            ->where('type', DataCollectionTransferOut::class)
            ->when($this->dataCollection_id, function ($query) {
                return $query->where('id', $this->dataCollection_id);
            })
            ->get();

        foreach ($dataCollections as $dataCollection) {
            $this->recreateReservationsFor($dataCollection);
        }
    }

    public function recreateReservationsFor(mixed $dataCollection): void
    {
        $dataCollectionRecords = $dataCollection->records;

        DB::statement('DROP TEMPORARY TABLE IF EXISTS temp_inventory_reservations');

        DB::statement('CREATE TEMPORARY TABLE temp_inventory_reservations AS
                SELECT
                    dcr.id,
                    dcr.inventory_id,
                    dcr.quantity_requested,
                    dcr.warehouse_code,
                    CONCAT("data_collection_id:", dcr.data_collection_id, ":data_collection_record_id:", dcr.id) as custom_uuid,
                    p.sku as product_sku,
                    dc.name as comment

                FROM data_collection_records dcr
                JOIN products p
                    ON dcr.product_id = p.id
                LEFT JOIN data_collections dc
                    ON dcr.data_collection_id = dc.id
                WHERE dcr.data_collection_id = ?', [$dataCollection->id]);

        DB::statement('
                INSERT INTO inventory_reservations (
                    custom_uuid,
                    inventory_id,
                    quantity_reserved,
                    warehouse_code,
                    product_sku,
                    created_at,
                    comment
                )
                SELECT custom_uuid, inventory_id, quantity_requested, warehouse_code, product_sku, now(), comment
                FROM temp_inventory_reservations
                ON DUPLICATE KEY UPDATE quantity_reserved = VALUES(quantity_reserved)
            ');

        DB::statement('DROP TEMPORARY TABLE IF EXISTS temp_inventory_reservations');

        $recordIds = $dataCollectionRecords->pluck('id');
        DataCollectionRecord::query()
            ->whereIn('id', $recordIds)
            ->update(['is_reserved' => true]);

        $inventoryIds = collect($dataCollectionRecords->pluck('inventory_id'));
        QuantityReservedService::recalculateQuantityReserved($inventoryIds);

        Inventory::query()
            ->whereIn('id', $inventoryIds)
            ->update(['recount_required' => 1]);
    }
}
