<?php

namespace Tests\Modules\Api2cart\src\Jobs;
use App\Modules\Api2cart\src\Jobs\EnsureAllProductLinksExistJob;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EnsureAllProductLinksExistJobTest extends TestCase
{
    #[Test]
    public function test_job()
    {
        // Dispatch the job synchronously
        EnsureAllProductLinksExistJob::dispatchSync();

        // No exceptions should be thrown, so we can consider the test passed
        $this->assertTrue(true, 'EnsureAllProductLinksExistJob executed without exceptions.');
    }
}
