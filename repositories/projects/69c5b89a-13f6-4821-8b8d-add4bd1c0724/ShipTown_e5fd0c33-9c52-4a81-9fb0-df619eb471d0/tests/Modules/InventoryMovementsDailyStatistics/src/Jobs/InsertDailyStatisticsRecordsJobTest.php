<?php

namespace Tests\Modules\InventoryMovementsDailyStatistics\src\Jobs;

use App\Abstracts\JobTestAbstract;
use App\Modules\InventoryMovementsDailyStatistics\src\Jobs\InsertDailyStatisticsRecordsJob;
use App\Modules\InventoryMovementsDailyStatistics\src\Models\InventoryMovementsDailyStatistic;

class InsertDailyStatisticsRecordsJobTest extends JobTestAbstract
{
    public function test_job()
   {
       InventoryMovementsDailyStatistic::factory()->create();

       InsertDailyStatisticsRecordsJob::dispatchSync();

       $this->assertCount(1, InventoryMovementsDailyStatistic::query()->get());
    }
}
