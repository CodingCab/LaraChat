<?php

namespace App\Modules\InventoryMovementsDailyStatistics\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Models\Inventory;
use App\Modules\InventoryMovementsDailyStatistics\src\Models\InventoryMovementsDay;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InsertDailyStatisticsRecordsJob extends UniqueJob
{
    public function handle(): void
    {
        InventoryMovementsDay::query()
            ->where(['recalc_required' => true])
            ->chunkById(100, function (Collection $records) {
                $records->each(function (InventoryMovementsDay $day) {
                    $this->recalculate($day);
                });

                // 50ms delay to avoid overwhelming the database
                usleep(50000);
            });
    }

    public function recalculate(InventoryMovementsDay $day): void
    {
        $batchSize = 500;
        $maxInventoryID = Inventory::query()->max('id') ?? 0;

        while ($day->max_inventory_id_checked < $maxInventoryID) {
            $maxIdToCheck = min($day->max_inventory_id_checked + $batchSize, $maxInventoryID);
            DB::affectingStatement('
                INSERT INTO inventory_movements_daily_statistics (
                    date,
                    warehouse_code,
                    inventory_id,
                    created_at,
                    updated_at
                )

                SELECT
                    ? as date,
                    inventory.warehouse_code as warehouse_code,
                    inventory.id as inventory_id,
                    now() as created_at,
                    now() as updated_at
                FROM inventory

                WHERE inventory.id BETWEEN ? AND ?
                    AND DATE(inventory.first_movement_at) <= ?
                    AND (DATE(inventory.last_movement_at) >= ? OR inventory.quantity != 0)

                ON DUPLICATE KEY UPDATE
                    recalc_required = true,
                    updated_at = now()
            ', [
                $day->date->toDateString(),
                $day->max_inventory_id_checked,
                $maxIdToCheck,
                $day->date->toDateString(),
                $day->date->toDateString(),
            ]);

            $day->update(['max_inventory_id_checked' => $maxIdToCheck]);
        }

        $day->update(['recalc_required' => false]);
    }
}
