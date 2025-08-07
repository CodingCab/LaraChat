<?php

namespace Tests\Modules\Api2cart\src\Jobs;

use App;
use App\Abstracts\JobTestAbstract;

class DispatchSyncOrderStatusJobsJobTest extends JobTestAbstract
{
    public function test_job()
   {
        App\Modules\Api2cart\src\Jobs\DispatchSyncOrderStatusJobsJob::dispatchSync();

        $this->assertTrue(true, 'Job did not throw any exceptions');
    }
}
