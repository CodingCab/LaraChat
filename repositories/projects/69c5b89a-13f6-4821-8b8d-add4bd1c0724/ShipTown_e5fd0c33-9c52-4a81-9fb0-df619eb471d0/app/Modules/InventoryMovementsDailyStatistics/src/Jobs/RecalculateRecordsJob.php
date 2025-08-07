<?php

namespace App\Modules\InventoryMovementsDailyStatistics\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Modules\InventoryMovementsDailyStatistics\src\Models\InventoryMovementsDailyStatistic;
use Illuminate\Support\Facades\DB;

class RecalculateRecordsJob extends UniqueJob
{
    public function handle(): void
    {
        InventoryMovementsDailyStatistic::query()
            ->where(['recalc_required' => true])
            ->chunkById(100, function ($records) {
                $this->recalculate($records);

                // 50ms delay to avoid overwhelming the database
                usleep(50000);
            });
    }

    private function recalculate($records): void
    {
        InventoryMovementsDailyStatistic::query()
            ->whereIn('id', $records->pluck('id'))
            ->update([
                'recalc_required' => false,
                'last_inventory_movement_id' => DB::raw('(
                    SELECT im.id

                    FROM inventory_movements as im

                    WHERE im.inventory_id = inventory_movements_daily_statistics.inventory_id
                        AND DATE(im.occurred_at) <= inventory_movements_daily_statistics.date

                    ORDER BY im.sequence_number DESC

                    LIMIT 1
                )')
            ]);
    }
}
