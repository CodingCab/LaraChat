<?php

namespace App\Modules\Rmsapi\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Modules\Rmsapi\src\Models\RmsapiSaleImport;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ProcessImportedSalesRecordsJob extends UniqueJob
{
    public function handle(): bool
    {
        $batch_size = 50;

        do {
            $this->processImportedRecords($batch_size);

            Log::info('RMSAPI ProcessImportedSalesRecordsJob Processed imported sales records', [
                'count' => $batch_size,
            ]);

            $hasNoRecordsToProcess = RmsapiSaleImport::query()
                ->whereNull('reserved_at')
                ->whereNull('processed_at')
                ->whereNotNull('inventory_id')
                ->whereNotNull('product_id')
                ->whereNotNull('warehouse_id')
                ->where('comment', 'not like', 'PM_OrderProductShipment_%')
                ->exists();

            usleep(100000); // 0.1 sec
        } while ($hasNoRecordsToProcess);

        return true;
    }

    private function processImportedRecords(int $batch_size): void
    {
        $records = RmsapiSaleImport::query()
            ->whereNull('reserved_at')
            ->whereNull('processed_at')
            ->whereNotNull('inventory_id')
            ->whereNotNull('product_id')
            ->whereNotNull('warehouse_id')
            ->where('comment', 'not like', 'PM_OrderProductShipment_%')
            ->with('inventory')
            ->orderBy('id')
            ->limit($batch_size)
            ->get();

        $records->each(function (RmsapiSaleImport $salesRecord) {
            $product = Product::query()->where('id', $salesRecord->product_id)->first();

            $inventory = Inventory::query()
                ->where('id', $salesRecord->inventory_id)
                ->first();

            InventoryMovement::query()->updateOrCreate([
                'custom_unique_reference_id' => $salesRecord->uuid,
            ], [
                'inventory_id' => $salesRecord->inventory_id,
                'warehouse_code' => $salesRecord->warehouse->code,
                'warehouse_id' => $salesRecord->warehouse_id,
                'product_id' => $salesRecord->product_id,
                'sequence_number' => null,
                'occurred_at' => Carbon::createFromTimeString($salesRecord->transaction_time, 'Europe/Dublin')->utc(),
                'type' => $salesRecord->type === 'rms_sale' ? 'sale' : 'adjustment',
                'quantity_before' => $inventory->quantity ?? 0,
                'quantity_delta' => $salesRecord->quantity,
                'quantity_after' => ($inventory->quantity ?? 0) + $salesRecord->quantity,
                'unit_cost' => data_get($salesRecord, 'unit_cost', 0),
                'unit_price' => data_get($salesRecord, 'price', 0),
                'sales_tax_code' => data_get($product, 'default_tax_code', ''),
                'description' => $salesRecord->type === 'rms_sale' ? 'rms_sale' : 'rmsapi_inventory_movement',
                'updated_at' => now()->utc()->toDateTimeLocalString(),
            ]);

            $inventory->update([
                'quantity' => ($inventory->quantity ?? 0) + $salesRecord->quantity,
            ]);
        });

        RmsapiSaleImport::query()
            ->whereIn('id', $records->pluck('id')->toArray())
            ->update(['processed_at' => now()->utc()->toDateTimeLocalString()]);
    }
}
