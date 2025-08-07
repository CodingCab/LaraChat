<?php

namespace Tests\Modules\Magento2API\PriceSync\src\Jobs;

use App;
use App\Abstracts\JobTestAbstract;

class RecheckExistsInMagentoJobTest extends JobTestAbstract
{
    public function test_job()
   {
        App\Modules\Magento2API\PriceSync\src\Jobs\RecheckExistsInMagentoJob::dispatchSync();

        $this->assertTrue(true, 'Job did not throw any exceptions');
    }
}
