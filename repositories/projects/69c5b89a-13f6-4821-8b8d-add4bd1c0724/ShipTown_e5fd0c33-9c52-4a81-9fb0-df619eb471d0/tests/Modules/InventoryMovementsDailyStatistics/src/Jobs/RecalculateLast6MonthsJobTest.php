<?php

namespace Tests\Modules\InventoryMovementsDailyStatistics\src\Jobs;

use App;
use Tests\TestCase;

class RecalculateLast6MonthsJobTest extends TestCase
{
    public function test_job()
   {
        App\Modules\InventoryMovementsDailyStatistics\src\Jobs\RecalculateLast6MonthsJob::dispatchSync();

        $this->assertTrue(true, 'Job did not throw any exceptions');
    }
}
