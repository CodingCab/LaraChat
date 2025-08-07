<?php

namespace Tests\Modules\InventoryMovementsDailyStatistics\src\Jobs;

use App;
use App\Abstracts\JobTestAbstract;

class RecalculateRecordsJobTest extends JobTestAbstract
{
    public function test_job()
   {
        App\Modules\InventoryMovementsDailyStatistics\src\Jobs\RecalculateRecordsJob::dispatchSync();

        $this->assertTrue(true, 'Job did not throw any exceptions');
    }
}
