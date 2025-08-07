<?php

namespace Tests\Modules\Api2cart\src\Jobs\old;

use App;
use App\Abstracts\JobTestAbstract;

class FetchVariantsInfoJobTest extends JobTestAbstract
{
    public function test_job()
   {
        App\Modules\Api2cart\src\Jobs\old\FetchVariantsInfoJob::dispatchSync();

        $this->assertTrue(true, 'Job did not throw any exceptions');
    }
}
