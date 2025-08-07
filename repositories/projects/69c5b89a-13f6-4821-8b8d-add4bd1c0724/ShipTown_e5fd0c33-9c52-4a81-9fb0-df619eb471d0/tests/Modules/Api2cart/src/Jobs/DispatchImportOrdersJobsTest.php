<?php

namespace Tests\Modules\Api2cart\src\Jobs;
use App\Modules\Api2cart\src\Jobs\DispatchImportOrdersJobs;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DispatchImportOrdersJobsTest extends TestCase
{
    #[Test]
    public function test_job()
    {
        DispatchImportOrdersJobs::dispatch();

        // no exceptions should be thrown, so we can consider the test passed
        $this->assertTrue(true, 'DispatchImportOrdersJobs executed without exceptions.');
    }
}
