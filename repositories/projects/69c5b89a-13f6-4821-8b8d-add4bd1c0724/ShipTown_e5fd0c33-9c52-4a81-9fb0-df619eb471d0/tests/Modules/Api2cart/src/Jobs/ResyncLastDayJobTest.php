<?php

namespace Tests\Modules\Api2cart\src\Jobs;
use App\Modules\Api2cart\src\Jobs\ResyncLastDayJob;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ResyncLastDayJobTest extends TestCase
{
    #[Test]
    public function test_job()
    {
        ResyncLastDayJob::dispatchSync();

        $this->assertTrue(true, 'ResyncLastDayJob executed without exceptions.');
    }
}
