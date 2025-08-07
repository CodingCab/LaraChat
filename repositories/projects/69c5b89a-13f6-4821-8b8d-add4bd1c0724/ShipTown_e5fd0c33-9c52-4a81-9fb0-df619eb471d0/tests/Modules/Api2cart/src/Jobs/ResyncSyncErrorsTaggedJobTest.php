<?php

namespace Tests\Modules\Api2cart\src\Jobs;
use App\Modules\Api2cart\src\Jobs\ResyncSyncErrorsTaggedJob;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ResyncSyncErrorsTaggedJobTest extends TestCase
{
    #[Test]
    public function test_job()
    {
        // Dispatch the job synchronously
        ResyncSyncErrorsTaggedJob::dispatchSync();

        // No exceptions should be thrown, so we can consider the test passed
        $this->assertTrue(true, 'ResyncSyncErrorsTaggedJob executed without exceptions.');
    }
}
