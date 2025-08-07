<?php

namespace Tests\Modules\Api2cart\src\Jobs\old;

use App;
use App\Abstracts\JobTestAbstract;

class FetchSimpleProductsInfoJobTest extends JobTestAbstract
{
    public function test_job()
   {
        App\Modules\Api2cart\src\Jobs\old\FetchSimpleProductsInfoJob::dispatchSync();

        $this->assertTrue(true, 'Job did not throw any exceptions');
    }
}
