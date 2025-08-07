<?php

namespace Tests\Modules\InventoryMovements\src\Jobs;
use PHPUnit\Framework\Attributes\Test;

use App\Modules\InventoryMovements\src\Jobs\InventoryQuantityCheckJob;
use Tests\TestCase;

class InventoryQuantityCheckJobTest extends TestCase
{
    #[Test]
    public function test_job()
    {
        InventoryQuantityCheckJob::dispatch();
        $this->markTestIncomplete('This test has not been implemented yet');
    }
}
