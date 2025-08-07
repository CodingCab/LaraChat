<?php

namespace Tests\Modules\DataCollectorOfflineInventoryReserveStock;
use PHPUnit\Framework\Attributes\Test;

use App\Modules\DataCollectorOfflineInventoryReserveStock\src\DataCollectorOfflineInventoryReserveStockServiceProvider;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        DataCollectorOfflineInventoryReserveStockServiceProvider::enableModule();
    }

    #[Test]
    public function testBasicFunctionality()
    {
        $this->assertTrue(true, 'Each job has its own tests');
    }
}
