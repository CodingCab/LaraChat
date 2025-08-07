<?php

namespace App\Jobs;

use App\Abstracts\UniqueJob;
use App\Models\InventoryMovement;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateInventoryMovementProductDetailsJob extends UniqueJob
{

    public $timeout = 540; // 9 minutes (540 seconds)

    public function handle(): void
    {
        $startTime = Carbon::now();
        $maxExecutionTime = 9 * 60; // 9 minutes in seconds
        $batchSize = 1000;
        $updatedCount = 0;

        Log::info('UpdateInventoryMovementProductDetailsJob started');

        try {
            do {
                // Check if we're approaching the time limit
                if (Carbon::now()->diffInSeconds($startTime) >= $maxExecutionTime - 30) {
                    Log::info('UpdateInventoryMovementProductDetailsJob approaching time limit, stopping', [
                        'updated_count' => $updatedCount,
                        'execution_time' => Carbon::now()->diffInSeconds($startTime) . ' seconds'
                    ]);
                    break;
                }

                // Update inventory movements with null product details
                $updated = DB::table('inventory_movements')
                    ->join('products', 'inventory_movements.product_id', '=', 'products.id')
                    ->where(function ($query) {
                        $query->whereNull('inventory_movements.sku')
                            ->orWhereNull('inventory_movements.name')
                            ->orWhereNull('inventory_movements.department')
                            ->orWhereNull('inventory_movements.category');
                    })
                    ->limit($batchSize)
                    ->update([
                        'inventory_movements.sku' => DB::raw('products.sku'),
                        'inventory_movements.name' => DB::raw('products.name'),
                        'inventory_movements.department' => DB::raw('products.department'),
                        'inventory_movements.category' => DB::raw('products.category'),
                        'inventory_movements.updated_at' => now(),
                    ]);

                $updatedCount += $updated;

                // If we updated less than the batch size, we're done
                if ($updated < $batchSize) {
                    break;
                }

                // Small delay to prevent overloading the database
                usleep(100000); // 0.1 second
            } while ($updated > 0);

            Log::info('UpdateInventoryMovementProductDetailsJob completed', [
                'updated_count' => $updatedCount,
                'execution_time' => Carbon::now()->diffInSeconds($startTime) . ' seconds'
            ]);
        } catch (\Exception $e) {
            Log::error('UpdateInventoryMovementProductDetailsJob failed', [
                'error' => $e->getMessage(),
                'updated_count' => $updatedCount,
                'execution_time' => Carbon::now()->diffInSeconds($startTime) . ' seconds'
            ]);
            throw $e;
        }
    }
}
