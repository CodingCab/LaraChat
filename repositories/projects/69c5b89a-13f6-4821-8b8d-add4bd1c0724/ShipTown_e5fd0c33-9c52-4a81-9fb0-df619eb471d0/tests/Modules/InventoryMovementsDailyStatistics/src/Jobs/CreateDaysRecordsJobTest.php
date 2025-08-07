<?php

namespace Tests\Modules\InventoryMovementsDailyStatistics\src\Jobs;

use App\Abstracts\JobTestAbstract;
use App\Models\InventoryMovement;
use App\Modules\InventoryMovementsDailyStatistics\src\Jobs\CreateDaysRecordsJob;
use App\Modules\InventoryMovementsDailyStatistics\src\Models\InventoryMovementsDay;

class CreateDaysRecordsJobTest extends JobTestAbstract
{
    public function test_job()
   {
       /** @var InventoryMovement $firstInventoryMovement */
       InventoryMovement::factory()->create(['occurred_at' => now()->subDays(5)]);
       InventoryMovement::factory()->create(['occurred_at' => now()->subDays(4)]);
       InventoryMovement::factory()->create(['occurred_at' => now()->subDays(3)]);
       InventoryMovement::factory()->create(['occurred_at' => now()->subDays(2)]);
       InventoryMovement::factory()->create(['occurred_at' => now()->subDay()]);
       InventoryMovement::factory()->create(['occurred_at' => now()]);

       CreateDaysRecordsJob::dispatchSync();

       ray(InventoryMovementsDay::query()->get()->toArray(), InventoryMovement::query()->get()->toArray());
       $this->assertCount(6, InventoryMovementsDay::query()->get());
    }
}
