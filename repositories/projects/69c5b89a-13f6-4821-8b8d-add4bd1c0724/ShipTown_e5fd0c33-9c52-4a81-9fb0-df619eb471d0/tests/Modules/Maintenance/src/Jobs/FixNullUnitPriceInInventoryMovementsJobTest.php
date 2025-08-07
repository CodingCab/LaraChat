<?php

namespace Tests\Modules\Maintenance\src\Jobs;

use App;
use App\Abstracts\JobTestAbstract;

class FixNullUnitPriceInInventoryMovementsJobTest extends JobTestAbstract
{
    public function test_job()
    {
        App\Modules\Maintenance\src\Jobs\FixNullUnitPriceInInventoryMovementsJob::dispatchSync();

        $this->assertTrue(true, 'Job did not throw any exceptions');
    }
}
