<?php

namespace Tests\Modules\Telescope\src\Jobs;
use PHPUnit\Framework\Attributes\Test;

use App\Modules\Telescope\src\Jobs\PruneEntriesJob;
use Tests\TestCase;

class PruneEntriesJobTest extends TestCase
{
    #[Test]
    public function test_job()
    {
        PruneEntriesJob::dispatchSync();

        $this->assertTrue(true,'Job ran successfully');
    }
}
