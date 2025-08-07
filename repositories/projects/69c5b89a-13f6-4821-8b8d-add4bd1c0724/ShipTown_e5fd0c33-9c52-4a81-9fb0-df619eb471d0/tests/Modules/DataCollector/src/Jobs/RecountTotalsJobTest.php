<?php

namespace Tests\Modules\DataCollector\src\Jobs;
use PHPUnit\Framework\Attributes\Test;

use App\Modules\DataCollector\src\Jobs\RecountTotalsJob;
use Tests\TestCase;

class RecountTotalsJobTest extends TestCase
{
    #[Test]
    public function test_job()
    {
        $job = new RecountTotalsJob();
        $job->handle();

        $this->assertTrue(true, 'Job did not throw any exceptions');
    }
}
