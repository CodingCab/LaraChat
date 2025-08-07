<?php

namespace Tests\Modules\DataCollectorTransfersOutReserveStock;
use PHPUnit\Framework\Attributes\Test;

use App\Modules\DataCollectorTransfersOutReserveStock\src\DataCollectorTransfersOutReserveStockServiceProvider;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        DataCollectorTransfersOutReserveStockServiceProvider::enableModule();
    }

    #[Test]
    public function testBasicFunctionality(): void
    {
        $this->assertTrue(true, 'Each job has its own tests');
    }
}
