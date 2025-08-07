<?php

namespace Tests\Modules\Magento2API\PriceSync\src\Jobs;

use App;
use App\Abstracts\JobTestAbstract;

class GetProductIdsJobTest extends JobTestAbstract
{
    public function test_job()
   {
        App\Modules\Magento2API\PriceSync\src\Jobs\CheckIfProductsExistJob::dispatchSync();

        $this->assertTrue(true, 'Job did not throw any exceptions');
    }
}
