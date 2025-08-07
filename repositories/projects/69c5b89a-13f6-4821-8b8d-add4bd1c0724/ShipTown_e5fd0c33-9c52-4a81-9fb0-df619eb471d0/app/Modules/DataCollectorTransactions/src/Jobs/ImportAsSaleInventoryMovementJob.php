<?php

namespace App\Modules\DataCollectorTransactions\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Models\DataCollection;
use App\Models\DataCollectionRecord;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Modules\Inventory\src\Jobs\RecalculateInventoryRecordsJob;
use App\Modules\InventoryMovements\src\Jobs\SequenceNumberJob;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;

class ImportAsSaleInventoryMovementJob extends UniqueJob
{
    public int $dataCollection_id;

    public function __construct(int $dataCollection_id)
    {
        $this->dataCollection_id = $dataCollection_id;
    }

    public function uniqueId(): string
    {
        return implode('-', [get_class($this), $this->dataCollection_id]);
    }

    public function handle(): void
    {
        /** @var DataCollection $dataCollection */
        $dataCollection = DataCollection::withTrashed()->findOrFail($this->dataCollection_id);

        if ($dataCollection->deleted_at === null) {
            $dataCollection->delete();
        }

        $dataCollection->update(['custom_uuid' => null]);

        do {
            $dataCollectionRecords = $dataCollection->records()
                ->with('inventory')
                ->with('inventory.prices')
                ->whereNull('is_processed')
                ->limit(10)
                ->get();

            if ($dataCollectionRecords->isEmpty()) {
                $dataCollection->update(['currently_running_task' => null]);

                Bus::chain([
                    new SequenceNumberJob,
                    new RecalculateInventoryRecordsJob,
                ])->dispatch();
                return;
            }

            $inventoryMovementRecords = $dataCollectionRecords
                ->map(function (DataCollectionRecord $record) use ($dataCollection) {
                    $current_date_time = now()->utc()->toDateTimeLocalString();
                    $custom_uuid = implode('-', [
                        "data_collection_record_id_{$record->getKey()}",
                        "data_collection_records_updated_at_$current_date_time"
                    ]);

                    return [
                        'custom_unique_reference_id' => $custom_uuid,
                        'sequence_number' => null,
                        'occurred_at' => $dataCollection->deleted_at ?? $current_date_time,
                        'inventory_id' => $record->inventory_id,
                        'type' => InventoryMovement::TYPE_SALE,
                        'product_id' => $record->product_id,
                        'warehouse_id' => $dataCollection->warehouse->id,
                        'warehouse_code' => $dataCollection->warehouse->code,
                        'quantity_before' => $record->inventory->quantity,
                        'quantity_delta' => $record->quantity_scanned - $record->inventory->quantity,
                        'quantity_after' => $record->quantity_scanned,
                        'unit_cost' => $record->inventory->prices->cost,
                        'unit_price' => $record->inventory->prices->price,
                        'description' => "Transaction #$dataCollection->id",
                        'user_id' => Auth::id(),
                        'created_at' => $current_date_time,
                        'updated_at' => $current_date_time,
                    ];
                });

            DB::transaction(function () use ($dataCollection, $inventoryMovementRecords, $dataCollectionRecords) {
                $affectedRows = InventoryMovement::query()->upsert(
                    $inventoryMovementRecords->toArray(),
                    ['custom_unique_reference_id'],
                    ['sequence_number', 'quantity_after', 'updated_at', 'description']
                );

                if ($affectedRows > 0 && $inventoryMovementRecords->count() === $affectedRows) {
                    $dataCollectionRecords->each(function (DataCollectionRecord $record) {
                        $record->increment('total_transferred_out', $record->quantity_scanned);
                        $record->update(['quantity_scanned' => 0]);
                    });
                }

                DataCollectionRecord::query()
                    ->whereIn('id', $dataCollectionRecords->pluck('id'))
                    ->update(['is_processed' => true]);

                Inventory::query()
                    ->upsert($dataCollectionRecords->map(function (DataCollectionRecord $record) use ($dataCollection) {
                        return [
                            'id' => $record->inventory_id,
                            'product_sku' => $record->inventory->product_sku,
                            'quantity' => $record->quantity_scanned,
                            'warehouse_id' => $dataCollection->warehouse_id,
                            'warehouse_code' => $dataCollection->warehouse->code,
                            'product_id' => $record->product_id,
                            'last_movement_at' => now()->utc()->toDateTimeLocalString(),
                            'last_counted_at' => now()->utc()->toDateTimeLocalString(),
                        ];
                    })->toArray(), ['id'], ['quantity', 'last_movement_at', 'last_counted_at']);
            });
        } while ($dataCollectionRecords->isNotEmpty());
    }
}
