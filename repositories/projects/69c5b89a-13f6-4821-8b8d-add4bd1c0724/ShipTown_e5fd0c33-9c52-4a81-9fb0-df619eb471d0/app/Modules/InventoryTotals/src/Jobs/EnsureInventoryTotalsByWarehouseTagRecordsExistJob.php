<?php

namespace App\Modules\InventoryTotals\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Models\Product;
use App\Models\Taggable;
use App\Models\Warehouse;
use App\Modules\InventoryTotals\src\Models\InventoryTotalByWarehouseTag;
use Illuminate\Support\Facades\Log;

class EnsureInventoryTotalsByWarehouseTagRecordsExistJob extends UniqueJob
{
    public function handle(): void
    {
        $maxProductID = Product::query()->max('id');

        do {
            $minProductID = $maxProductID - 50;

            $recordsInserted = $this->ensureRecordsExists($minProductID, $maxProductID);

            Log::debug('Job processing', [
                'job' => self::class,
                'records created' => $recordsInserted,
                'minProductID' => $minProductID,
                'maxProductID' => $maxProductID,
            ]);

            $maxProductID = $minProductID;
            usleep(100000); // 0.1 sec
        } while ($minProductID > 0);
    }

    public function fail($exception = null)
    {
        Log::error('Job failed', ['job' => self::class, 'error' => $exception->getMessage()]);
        report($exception);
    }

    public static function ensureRecordsExists($minProductID, $maxProductID): int
    {
        $records = Taggable::query()
            ->leftJoin('inventory', 'taggables.taggable_id', '=', 'inventory.warehouse_id')
            ->where(['taggable_type' => Warehouse::class])
            ->whereBetween('inventory.product_id', [$minProductID, $maxProductID])
            ->get()
            ->map(function ($record) {
                return [
                    'tag_id' => data_get($record, 'tag_id'),
                    'product_id' => data_get($record, 'product_id'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();

        return InventoryTotalByWarehouseTag::query()->insertOrIgnore($records);
    }
}
