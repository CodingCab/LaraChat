<?php

namespace Tests\Modules\Api2cart\src\Jobs;

use App\Abstracts\JobTestAbstract;
use App\Modules\Api2cart\src\Jobs\ProcessImportedProductsJob;

class ProcessImportedProductsJobTest extends JobTestAbstract
{
    public function test_job()
   {
        ProcessImportedProductsJob::dispatchSync();

        // No exceptions should be thrown, so we can consider the test passed
        $this->assertTrue(true, 'ProcessImportedProductsJob executed without exceptions.');
    }
}
