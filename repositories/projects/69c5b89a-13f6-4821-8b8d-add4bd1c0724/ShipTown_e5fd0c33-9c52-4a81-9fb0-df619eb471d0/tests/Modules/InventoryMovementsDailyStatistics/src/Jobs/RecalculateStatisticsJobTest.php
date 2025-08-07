<?php

namespace Tests\Modules\InventoryMovementsDailyStatistics\src\Jobs;

use App\Abstracts\JobTestAbstract;
use App\Models\InventoryMovement;
use App\Modules\InventoryMovementsDailyStatistics\src\Jobs\RecalculateStatisticsJob;
use App\Modules\InventoryMovementsDailyStatistics\src\Models\InventoryMovementsDailyStatistic;

class RecalculateStatisticsJobTest extends JobTestAbstract
{
    public function test_job()
   {
       /** @var InventoryMovement $inventoryMovement */
       $inventoryMovement = InventoryMovement::factory()->create(['occurred_at' => now()]);

       RecalculateStatisticsJob::dispatchSync();

       /** @var InventoryMovementsDailyStatistic $firstStatistic */
       $firstStatistic = InventoryMovementsDailyStatistic::query()->first();

       $this->assertEquals($inventoryMovement->id, $firstStatistic->last_inventory_movement_id);
    }
}
