<?php

namespace Tests\Modules\InventoryMovements\src\Jobs;
use App\Modules\InventoryMovements\src\Jobs\CheckForIncorrectSequenceNumberJob;
use PHPUnit\Framework\Attributes\Test;

use Tests\TestCase;

class CheckForIncorrectSequenceNumberJobTest extends TestCase
{
    #[Test]
    public function test_job()
    {
        CheckForIncorrectSequenceNumberJob::dispatchSync();

        $this->assertTrue(true, 'Job dispatched successfully');
    }
}
