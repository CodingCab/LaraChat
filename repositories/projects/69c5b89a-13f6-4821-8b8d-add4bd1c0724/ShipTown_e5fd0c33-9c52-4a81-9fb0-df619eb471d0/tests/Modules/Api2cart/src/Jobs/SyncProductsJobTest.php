<?php

namespace Tests\Modules\Api2cart\src\Jobs;
use App\Modules\Api2cart\src\Jobs\SyncProductsJob;
use PHPUnit\Framework\Attributes\Test;

use Tests\TestCase;

class SyncProductsJobTest extends TestCase
{
    #[Test]
    public function test_job()
    {
        SyncProductsJob::dispatchSync();

        // No exceptions should be thrown, so we can consider the test passed
        $this->assertTrue(true, 'SyncProductsJob executed without exceptions.');
    }
}
