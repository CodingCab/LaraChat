<?php

namespace Tests\Modules\InventoryTotals\src\Jobs;

use App;
use App\Abstracts\JobTestAbstract;

class RecalculateProductTotalsTableJobTest extends JobTestAbstract
{
    public function test_job()
   {
        App\Modules\InventoryTotals\src\Jobs\RecalculateProductTotalsTableJob::dispatchSync();

        $this->assertTrue(true, 'Job did not throw any exceptions');
    }
}
